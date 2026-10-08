#!/bin/bash

keep_terminal_open() {
    local exit_code=$?
    echo ""
    read -r -p "Pengujian selesai. Tekan [ENTER] untuk menutup..."
    trap - EXIT
    exit "$exit_code"
}
trap keep_terminal_open EXIT

# ==============================================================================
# SKRIP PENGUJIANKU OTORISASI REST API (LARAVEL 12 SANCTUM)
# ==============================================================================

# Konfigurasi Environment
BASE_URL="${BASE_URL:-http://127.0.0.1:8000/api/v1}"

# Akun skenario IDOR dari database lokal
MAHASISWA_EMAIL="mahasiswa@kampuslms.test"
DOSEN_A_EMAIL="dosen-a@kampuslms.test"
DOSEN_B_EMAIL="dosen-b@kampuslms.test"
PASSWORD="1234"

for command in curl jq; do
    if ! command -v "$command" >/dev/null 2>&1; then
        echo "[ERROR] Perintah '$command' wajib tersedia."
        exit 1
    fi
done

get_token() {
    local email=$1
    local response http_code body token attempt=0

    while [ "$attempt" -le 2 ]; do
        response=$(curl -sS -w $'\n%{http_code}' -X POST "$BASE_URL/auth/login" \
            -H "Content-Type: application/json" \
            -H "Accept: application/json" \
            -d "{\"email\":\"$email\",\"password\":\"$PASSWORD\"}") || {
            echo "[ERROR] Tidak dapat menghubungi endpoint login: $BASE_URL/auth/login" >&2
            return 1
        }
        http_code=${response##*$'\n'}
        body=${response%$'\n'*}

        if [ "$http_code" = "429" ]; then
            if [ "$attempt" -ge 2 ]; then
                echo "[ERROR] Login $email tetap dibatasi setelah 3 percobaan. Coba lagi nanti." >&2
                return 1
            fi
            echo "[INFO] Login terkena batas 5 kali/menit (HTTP 429); menunggu 60 detik sebelum mencoba ulang." >&2
            sleep 60
            attempt=$((attempt + 1))
            continue
        fi

        if [ "$http_code" != "200" ]; then
            echo "[ERROR] Login $email gagal (HTTP $http_code): $(jq -r '.message // "respons tidak valid"' <<< "$body")" >&2
            return 1
        fi

        token=$(jq -r '.data.token // .token // .access_token // empty' <<< "$body")
        if [ -z "$token" ]; then
            echo "[ERROR] Respons login tidak berisi token untuk $email." >&2
            return 1
        fi
        printf '%s' "$token"
        return 0
    done
}

api_get() {
    local token=$1
    local url=$2
    curl -fsS "$url" \
        -H "Accept: application/json" \
        -H "Authorization: Bearer $token"
}

fetch_all_courses() {
    local token=$1
    local page=1
    local last_page=1
    local response
    local courses='[]'

    while [ "$page" -le "$last_page" ]; do
        response=$(api_get "$token" "$BASE_URL/courses?page=$page") || return 1
        courses=$(jq -cn --argjson current "$courses" --argjson response "$response" \
            '$current + $response.data')
        last_page=$(jq -r '.meta.last_page // 1' <<< "$response")
        page=$((page + 1))
    done

    printf '%s' "$courses"
}

echo "======================================================================"
echo "1. PERSIAPAN TOKEN SANCTUM VIA LOGIN"
echo "======================================================================"

TOKEN_MAHASISWA=$(get_token "$MAHASISWA_EMAIL")
TOKEN_DOSEN_A=$(get_token "$DOSEN_A_EMAIL")

for account in "mahasiswa:$TOKEN_MAHASISWA" "dosen:$TOKEN_DOSEN_A"; do
    if [ -z "${account#*:}" ]; then
        echo "[ERROR] Gagal login akun ${account%%:*}. Periksa server, email, dan nilai PASSWORD di skrip."
        exit 1
    fi
done

ALL_COURSES=$(fetch_all_courses "$TOKEN_DOSEN_A") || {
    echo "[ERROR] Course milik dosen tidak dapat diambil dari API. Pastikan server berjalan di $BASE_URL."
    exit 1
}

COURSE_ID=$(jq -r '.[0].id // empty' <<< "$ALL_COURSES")
COURSE_ID=${COURSE_ID//$'\r'/}

if [ -z "$COURSE_ID" ]; then
    echo "[ERROR] Tidak ditemukan course yang dimiliki $DOSEN_A_EMAIL. Periksa database."
    exit 1
fi

TOKEN_DOSEN_B=$(get_token "$DOSEN_B_EMAIL")
if [ -z "$TOKEN_DOSEN_B" ]; then
    echo "[ERROR] Gagal login dosen pembanding: $DOSEN_B_EMAIL."
    exit 1
fi

ASSIGNMENTS_JSON=$(api_get "$TOKEN_DOSEN_A" "$BASE_URL/courses/$COURSE_ID/assignments") || {
    echo "[ERROR] Assignment untuk course $COURSE_ID tidak dapat diambil."
    exit 1
}
mapfile -t ASSIGNMENT_IDS < <(jq -r '.data[].id' <<< "$ASSIGNMENTS_JSON")
ASSIGNMENT_ID=""
SUBMISSION_ID=""
GRADE_EXPECTED_CODE=201

for candidate_id in "${ASSIGNMENT_IDS[@]}"; do
    candidate_id=${candidate_id//$'\r'/}
    if [[ ! "$candidate_id" =~ ^[0-9]+$ ]]; then
        echo "[WARN] ID assignment tidak valid dari respons API: '$candidate_id'."
        continue
    fi

    SUBMISSIONS_URL="$BASE_URL/assignments/$candidate_id/submissions"
    SUBMISSIONS_JSON=$(api_get "$TOKEN_DOSEN_A" "$SUBMISSIONS_URL") || {
        echo "[WARN] Gagal membaca endpoint: $SUBMISSIONS_URL"
        continue
    }
    SUBMISSION_ID=$(jq -r '[.data[] | select(.grade == null)][0].id // empty' <<< "$SUBMISSIONS_JSON")

    if [ -n "$SUBMISSION_ID" ]; then
        ASSIGNMENT_ID=$candidate_id
        break
    fi

    SUBMISSION_ID=$(jq -r '.data[0].id // empty' <<< "$SUBMISSIONS_JSON")
    if [ -n "$SUBMISSION_ID" ]; then
        ASSIGNMENT_ID=$candidate_id
        GRADE_EXPECTED_CODE=200
        break
    fi
done

if [ -z "$ASSIGNMENT_ID" ] || [ -z "$SUBMISSION_ID" ]; then
    echo "[ERROR] Tidak ditemukan submission pada course $COURSE_ID. Assignment dari API: ${ASSIGNMENT_IDS[*]:-(kosong)}."
    exit 1
fi

echo "Token Mahasiswa : ${TOKEN_MAHASISWA:0:15}..."
echo "Token Dosen A   : ${TOKEN_DOSEN_A:0:15}..."
echo "Token Dosen B   : ${TOKEN_DOSEN_B:0:15}..."
echo "Course/Assignment/Submission: $COURSE_ID / $ASSIGNMENT_ID / $SUBMISSION_ID"
echo ""

# Helper function untuk menjalankan curl dan mencetak HTTP Status Code
run_test() {
    local label=$1
    local expected_code=$2
    local method=$3
    local url=$4
    local token=$5
    local data=$6

    local -a headers=(-H "Accept: application/json" -H "Content-Type: application/json")
    if [ -n "$token" ]; then
        headers+=(-H "Authorization: Bearer $token")
    fi

    if [ -n "$data" ]; then
        http_code=$(curl -s -o /dev/null -w "%{http_code}" -X "$method" "$url" "${headers[@]}" -d "$data")
    else
        http_code=$(curl -s -o /dev/null -w "%{http_code}" -X "$method" "$url" "${headers[@]}")
    fi

    if [ "$http_code" -eq "$expected_code" ]; then
        echo -e "  [PASS] $label | HTTP $http_code (Expected: $expected_code)"
    else
        echo -e "  [FAIL] $label | HTTP $http_code (Expected: $expected_code)"
        FAILURES=$((FAILURES + 1))
    fi
}

FAILURES=0

# ==============================================================================
# 2. EKSEKUSI SKENARIO PENGUJIAN
# ==============================================================================

echo "======================================================================"
echo "2. PENGUJIAN OTORISASI ENDPOINT"
echo "======================================================================"

# ------------------------------------------------------------------------------
# ENDPOINT: POST /v1/assignments (Buat Tugas)
# ------------------------------------------------------------------------------
echo -e "\n--- Testing: POST /assignments (Membuat Tugas) ---"
ASSIGNMENT_DATA='{"title":"Tugas Baru","instructions":"Kerjakan","due_at":"2099-12-31 23:59:00","status":"published","course_id":'$COURSE_ID'}'

# K1: Tanpa Token
run_test "1. Tanpa token" 401 "POST" "$BASE_URL/assignments" "" "$ASSIGNMENT_DATA"

# K2: Token Mahasiswa
run_test "2. Token Mahasiswa (Unauthorized Role)" 403 "POST" "$BASE_URL/assignments" "$TOKEN_MAHASISWA" "$ASSIGNMENT_DATA"

# K3: Token Dosen B pada Course Dosen A
ASSIGNMENT_DATA_DOSEN_A_COURSE='{"title":"Tugas Bajakan","instructions":"Test","due_at":"2099-12-31 23:59:00","status":"published","course_id":'$COURSE_ID'}'
run_test "3. Dosen B pada Course Dosen A (IDOR Protection)" 403 "POST" "$BASE_URL/assignments" "$TOKEN_DOSEN_B" "$ASSIGNMENT_DATA_DOSEN_A_COURSE"

# K4: Token Dosen A (Pemilik Course)
run_test "4. Dosen A (Valid Owner)" 201 "POST" "$BASE_URL/assignments" "$TOKEN_DOSEN_A" "$ASSIGNMENT_DATA"


# ------------------------------------------------------------------------------
# ENDPOINT: PUT /v1/assignments/{assignment} (Update Tugas)
# ------------------------------------------------------------------------------
echo -e "\n--- Testing: PUT /assignments/{assignment} (Update Tugas) ---"
UPDATE_DATA='{"title":"Tugas Updated","instructions":"Revisi","due_at":"2099-12-31 23:59:00","status":"published"}'

# K1: Tanpa Token
run_test "1. Tanpa token" 401 "PUT" "$BASE_URL/assignments/$ASSIGNMENT_ID" "" "$UPDATE_DATA"

# K2: Token Mahasiswa
run_test "2. Token Mahasiswa" 403 "PUT" "$BASE_URL/assignments/$ASSIGNMENT_ID" "$TOKEN_MAHASISWA" "$UPDATE_DATA"

# K3: Token Dosen B
run_test "3. Token Dosen B (Bukan Pemilik Assignment)" 403 "PUT" "$BASE_URL/assignments/$ASSIGNMENT_ID" "$TOKEN_DOSEN_B" "$UPDATE_DATA"

# K4: Token Dosen A
run_test "4. Token Dosen A (Valid Owner)" 200 "PUT" "$BASE_URL/assignments/$ASSIGNMENT_ID" "$TOKEN_DOSEN_A" "$UPDATE_DATA"


# ------------------------------------------------------------------------------
# ENDPOINT: GET /v1/assignments/{assignment}/submissions (Lihat List Submission)
# ------------------------------------------------------------------------------
echo -e "\n--- Testing: GET /assignments/{assignment}/submissions ---"

# K1: Tanpa Token
run_test "1. Tanpa token" 401 "GET" "$BASE_URL/assignments/$ASSIGNMENT_ID/submissions" ""

# K2: Token Mahasiswa
run_test "2. Token Mahasiswa" 403 "GET" "$BASE_URL/assignments/$ASSIGNMENT_ID/submissions" "$TOKEN_MAHASISWA"

# K3: Token Dosen B
run_test "3. Token Dosen B (Bukan Pengampu)" 403 "GET" "$BASE_URL/assignments/$ASSIGNMENT_ID/submissions" "$TOKEN_DOSEN_B"

# K4: Token Dosen A
run_test "4. Token Dosen A (Dosen Pengampu)" 200 "GET" "$BASE_URL/assignments/$ASSIGNMENT_ID/submissions" "$TOKEN_DOSEN_A"


# ------------------------------------------------------------------------------
# ENDPOINT: PUT /v1/submissions/{submission}/grade (Memberi Nilai Tugas)
# ------------------------------------------------------------------------------
echo -e "\n--- Testing: PUT /submissions/{submission}/grade (Memberi Nilai) ---"
GRADE_DATA='{"score":90,"feedback":"Sangat baik"}'

# K1: Tanpa Token
run_test "1. Tanpa token" 401 "PUT" "$BASE_URL/submissions/$SUBMISSION_ID/grade" "" "$GRADE_DATA"

# K2: Token Mahasiswa
run_test "2. Token Mahasiswa" 403 "PUT" "$BASE_URL/submissions/$SUBMISSION_ID/grade" "$TOKEN_MAHASISWA" "$GRADE_DATA"

# K3: Token Dosen B
run_test "3. Token Dosen B (Bukan Dosen Pengampu)" 403 "PUT" "$BASE_URL/submissions/$SUBMISSION_ID/grade" "$TOKEN_DOSEN_B" "$GRADE_DATA"

# K4: Token Dosen A
run_test "4. Token Dosen A (Dosen Pengampu Valid)" "$GRADE_EXPECTED_CODE" "PUT" "$BASE_URL/submissions/$SUBMISSION_ID/grade" "$TOKEN_DOSEN_A" "$GRADE_DATA"

echo -e "\n======================================================================"
echo "PENGUJIAN SELESAI"
echo "======================================================================"
if [ "$FAILURES" -gt 0 ]; then
    echo "$FAILURES pengujian gagal."
    exit 1
fi
echo "Semua pengujian berhasil."
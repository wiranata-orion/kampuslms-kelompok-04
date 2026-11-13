#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

if [[ "${1:-}" == "--local" ]]; then
    PHP_BIN="${PHP_BIN:-php}"
    APP_ENV=testing \
    DB_CONNECTION=sqlite \
    DB_DATABASE=:memory: \
    CACHE_STORE=array \
    SESSION_DRIVER=array \
    "$PHP_BIN" vendor/bin/phpunit --no-configuration --bootstrap vendor/autoload.php \
        tests/Feature/AuthorizationPolicyTest.php
    exit 0
fi

BASE_URL="${AUTHZ_BASE_URL:-https://lacikampus-production.up.railway.app}"
BASE_URL="${BASE_URL%/}"
API_BASE="${BASE_URL}/api/v1"
CURL_BIN="${CURL_BIN:-curl}"
CURL_TIMEOUT="${AUTHZ_TIMEOUT:-15}"
TEMP_DIR="$(mktemp -d)"
trap 'rm -rf "$TEMP_DIR"' EXIT

for command_name in "$CURL_BIN" jq; do
    if ! command -v "$command_name" >/dev/null 2>&1; then
        echo "FAIL $command_name tidak ditemukan." >&2
        exit 1
    fi
done

passed=0
failed=0

log_result() {
    local state="$1"
    local name="$2"
    local detail="${3:-}"

    printf '%-7s %s' "$state" "$name"
    [[ -n "$detail" ]] && printf ' (%s)' "$detail"
    printf '\n'
}

request() {
    local method="$1"
    local url="$2"
    local token="${3:-}"
    local data="${4:-}"
    local key="${5:-response}"
    local -a args

    args=(-sS --max-time "$CURL_TIMEOUT" -X "$method"
        -D "$TEMP_DIR/$key.headers" -o "$TEMP_DIR/$key.body"
        -w '%{http_code}' -H 'Accept: application/json')
    [[ -n "$token" ]] && args+=(-H "Authorization: Bearer $token")
    [[ -n "$data" ]] && args+=(-H 'Content-Type: application/json' --data "$data")
    "$CURL_BIN" "${args[@]}" "$url" >"$TEMP_DIR/$key.status"
}

status_is_expected() {
    local actual="$1"
    shift
    local expected
    for expected in "$@"; do
        [[ "$actual" == "$expected" ]] && return 0
    done
    return 1
}

expect_status() {
    local name="$1"
    local method="$2"
    local path="$3"
    local token="$4"
    shift 4
    local key="response_${passed}_${failed}"
    local status

    if ! request "$method" "$API_BASE$path" "$token" "" "$key"; then
        log_result FAIL "$name" "request error"
        failed=$((failed + 1))
        return
    fi

    status="$(cat "$TEMP_DIR/$key.status")"
    if status_is_expected "$status" "$@"; then
        log_result PASS "$name" "HTTP $status"
        passed=$((passed + 1))
    else
        log_result FAIL "$name" "expected $*, got HTTP $status"
        failed=$((failed + 1))
    fi
}

expect_public_status() {
    local name="$1"
    local method="$2"
    local path="$3"
    shift 3
    local key="public_${passed}_${failed}"
    local status

    if ! request "$method" "$BASE_URL$path" "" "" "$key"; then
        log_result FAIL "$name" "request error"
        failed=$((failed + 1))
        return
    fi

    status="$(cat "$TEMP_DIR/$key.status")"
    if status_is_expected "$status" "$@"; then
        log_result PASS "$name" "HTTP $status"
        passed=$((passed + 1))
    else
        log_result FAIL "$name" "expected $*, got HTTP $status"
        failed=$((failed + 1))
    fi
}

login() {
    local name="$1"
    local email="$2"
    local key="login_$name"
    local body
    local status

    body="$(jq -cn --arg email "$email" --arg password "${AUTHZ_PASSWORD:-password}" \
        '{email: $email, password: $password}')"
    if ! request POST "$API_BASE/auth/login" "" "$body" "$key"; then
        echo "FAIL login $name: request error" >&2
        return 1
    fi

    status="$(cat "$TEMP_DIR/$key.status")"
    if [[ "$status" != "200" ]]; then
        echo "FAIL login $name: HTTP $status" >&2
        return 1
    fi

    jq -er '.token // .data.token' "$TEMP_DIR/$key.body"
}

echo "Authorization smoke test: $BASE_URL"
echo

expect_public_status "Homepage tersedia" GET / 200
expect_public_status "Robots tersedia" GET /robots.txt 200 404
expect_status "API courses menolak guest" GET /courses "" 401
expect_status "API profile menolak guest" GET /me "" 401
expect_status "API dashboard menolak guest" GET /dashboard "" 401
expect_status "API users menolak guest" GET /users "" 401

invalid_login='{"email":"authz-check-invalid@example.invalid","password":"definitely-invalid"}'
login_key="invalid_login_${passed}_${failed}"
if request POST "$API_BASE/auth/login" "" "$invalid_login" "$login_key"; then
    login_status="$(cat "$TEMP_DIR/$login_key.status")"
    if status_is_expected "$login_status" 401 422; then
        log_result PASS "Login dengan kredensial invalid ditolak" "HTTP $login_status"
        passed=$((passed + 1))
    else
        log_result FAIL "Login dengan kredensial invalid ditolak" "HTTP $login_status"
        failed=$((failed + 1))
    fi
else
    log_result FAIL "Login dengan kredensial invalid ditolak" "request error"
    failed=$((failed + 1))
fi

ADMIN_TOKEN="${AUTHZ_ADMIN_TOKEN:-$(login admin admin@kampuslms.test)}"
LECTURER_TOKEN="${AUTHZ_LECTURER_TOKEN:-$(login dosen dosen@kampuslms.test)}"
STUDENT_TOKEN="${AUTHZ_STUDENT_TOKEN:-$(login mahasiswa mahasiswa@kampuslms.test)}"

expect_status "Token mahasiswa dapat membaca profile" GET /me "$STUDENT_TOKEN" 200
expect_status "Token mahasiswa dapat membaca dashboard" GET /dashboard "$STUDENT_TOKEN" 200
expect_status "Token mahasiswa dapat membaca course list" GET /courses "$STUDENT_TOKEN" 200
expect_status "Token mahasiswa dapat membaca course milik user" GET /my/courses "$STUDENT_TOKEN" 200
expect_status "Token mahasiswa dapat membaca submission milik user" GET /my/submissions "$STUDENT_TOKEN" 200
expect_status "Token mahasiswa dapat membaca ringkasan nilai sendiri" GET /my/grade-summary "$STUDENT_TOKEN" 200
expect_status "Token mahasiswa dapat membaca notifikasi" GET /notifications "$STUDENT_TOKEN" 200

request GET "$API_BASE/courses" "$LECTURER_TOKEN" "" lecturer_courses
request GET "$API_BASE/courses" "$STUDENT_TOKEN" "" student_courses
request GET "$API_BASE/me" "$LECTURER_TOKEN" "" lecturer_profile
request GET "$API_BASE/courses" "$ADMIN_TOKEN" "" admin_courses
COURSE_ID="${AUTHZ_COURSE_ID:-$(jq -er '.data[0].id' "$TEMP_DIR/lecturer_courses.body")}"
LECTURER_ID="$(jq -er '.data.id' "$TEMP_DIR/lecturer_profile.body")"
STUDENT_COURSE_IDS="$(jq -c '[.data[].id]' "$TEMP_DIR/student_courses.body")"
OTHER_COURSE_ID="${AUTHZ_OTHER_COURSE_ID:-$(jq -er --argjson student_ids "$STUDENT_COURSE_IDS" \
    '.data[] | select((.id as $id | $student_ids | index($id)) == null) | .id' \
    "$TEMP_DIR/lecturer_courses.body" | head -n 1)}"
OTHER_LECTURER_COURSE_ID="${AUTHZ_OTHER_LECTURER_COURSE_ID:-$(jq -er --argjson lecturer_id "$LECTURER_ID" \
    '.data[] | select(.lecturer_id != $lecturer_id) | .id' \
    "$TEMP_DIR/admin_courses.body" | head -n 1)}"

request GET "$API_BASE/courses/$COURSE_ID/assignments" "$LECTURER_TOKEN" "" course_assignments
DRAFT_ASSIGNMENT_ID="${AUTHZ_DRAFT_ASSIGNMENT_ID:-$(jq -er \
    '.data[] | select(.status == "draft") | .id' \
    "$TEMP_DIR/course_assignments.body" | head -n 1)}"
OTHER_USER_ID="${AUTHZ_OTHER_USER_ID:-1}"

expect_status "Mahasiswa ditolak dari course lain" GET "/courses/$OTHER_COURSE_ID" "$STUDENT_TOKEN" 403 404
expect_status "Mahasiswa ditolak dari roster course lain" GET "/courses/$OTHER_COURSE_ID/students" "$STUDENT_TOKEN" 403 404
expect_status "Mahasiswa ditolak dari draft assignment" GET "/assignments/$DRAFT_ASSIGNMENT_ID" "$STUDENT_TOKEN" 403 404
expect_status "Mahasiswa ditolak membaca user lain" GET "/users/$OTHER_USER_ID" "$STUDENT_TOKEN" 403 404
expect_status "Dosen ditolak dari course milik dosen lain" GET "/courses/$OTHER_LECTURER_COURSE_ID" "$LECTURER_TOKEN" 403 404
expect_status "Dosen ditolak dari roster course milik dosen lain" GET "/courses/$OTHER_LECTURER_COURSE_ID/students" "$LECTURER_TOKEN" 403 404
expect_status "Dosen ditolak membaca daftar user" GET /users "$LECTURER_TOKEN" 403
expect_status "Dosen ditolak membaca daftar lecturer" GET /lecturers "$LECTURER_TOKEN" 403
expect_status "Dosen ditolak mengubah user lain" PUT "/users/$OTHER_USER_ID" "$LECTURER_TOKEN" 403 404
expect_status "Dosen pengampu dapat membaca roster course" GET "/courses/$COURSE_ID/students" "$LECTURER_TOKEN" 200
expect_status "Dosen pengampu dapat membaca kandidat enrollment" GET "/courses/$COURSE_ID/enrollments/candidates" "$LECTURER_TOKEN" 200
expect_status "Admin dapat membaca daftar user" GET /users "$ADMIN_TOKEN" 200
expect_status "Admin dapat membaca daftar lecturer" GET /lecturers "$ADMIN_TOKEN" 200

echo
echo "Hasil: $passed passed, $failed failed"
if (( failed > 0 )); then
    exit 1
fi

<script setup>
import QRCode from 'qrcode';
import { computed, onMounted, reactive, ref } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api, { getErrorMessage } from '../../api';
import PageFrame from '../../components/PageFrame.vue';

const route = useRoute();
const router = useRouter();
const course = ref(null);
const user = ref(null);
const schedules = ref([]);
const sessions = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref('');
const message = ref('');
const conflicts = ref([]);
const suggestions = ref([]);
const qr = ref(null);
const attendanceCodes = reactive({});
const attendanceInputs = reactive({});
const attendanceMessages = reactive({});
const editingScheduleId = ref(null);
const qrSessionId = ref(null);
const form = reactive({
    weekday: 1,
    starts_at: '08:00',
    ends_at: '09:40',
    room: '',
    effective_from: '',
    effective_until: '',
});
const weekdays = [
    { value: 1, label: 'Senin' },
    { value: 2, label: 'Selasa' },
    { value: 3, label: 'Rabu' },
    { value: 4, label: 'Kamis' },
    { value: 5, label: 'Jumat' },
    { value: 6, label: 'Sabtu' },
    { value: 7, label: 'Minggu' },
];
const canManage = computed(() => ['admin', 'dosen'].includes(user.value?.role));

function todayInMakassar() {
    return new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Makassar' }).format(new Date());
}

function shiftDate(date, days) {
    const value = new Date(`${date}T12:00:00+08:00`);
    value.setDate(value.getDate() + days);
    return new Intl.DateTimeFormat('en-CA', { timeZone: 'Asia/Makassar' }).format(value);
}

function formatSessionTime(value) {
    return new Intl.DateTimeFormat('id-ID', {
        timeZone: 'Asia/Makassar',
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(value));
}

function weekdayName(day) {
    return weekdays.find((item) => item.value === day)?.label ?? '—';
}

async function load() {
    loading.value = true;
    error.value = '';
    try {
        const courseId = route.params.id;
        const [userResponse, courseResponse, scheduleResponse, sessionResponse] = await Promise.all([
            api.get('/me'),
            api.get(`/courses/${courseId}`),
            api.get(`/courses/${courseId}/schedules`),
            api.get(`/courses/${courseId}/sessions`, {
                params: { from: todayInMakassar(), until: shiftDate(todayInMakassar(), 30), page: 1 },
            }),
        ]);
        user.value = userResponse.data.data;
        course.value = courseResponse.data.data;
        schedules.value = scheduleResponse.data.data;
        sessions.value = sessionResponse.data.data;
    } catch (requestError) {
        error.value = getErrorMessage(requestError);
    } finally {
        loading.value = false;
    }
}

function resetForm() {
    editingScheduleId.value = null;
    Object.assign(form, {
        weekday: 1,
        starts_at: '08:00',
        ends_at: '09:40',
        room: '',
        effective_from: '',
        effective_until: '',
    });
    conflicts.value = [];
    suggestions.value = [];
}

function editSchedule(schedule) {
    editingScheduleId.value = schedule.id;
    Object.assign(form, {
        weekday: schedule.weekday,
        starts_at: schedule.starts_at,
        ends_at: schedule.ends_at,
        room: schedule.room,
        effective_from: schedule.effective_from,
        effective_until: schedule.effective_until,
    });
    conflicts.value = [];
    suggestions.value = [];
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

async function saveSchedule() {
    saving.value = true;
    error.value = '';
    message.value = '';
    conflicts.value = [];
    suggestions.value = [];

    try {
        const courseId = route.params.id;
        if (editingScheduleId.value) {
            await api.put(`/course-schedules/${editingScheduleId.value}`, form);
            message.value = 'Pola jadwal berhasil diperbarui.';
        } else {
            await api.post(`/courses/${courseId}/schedules`, form);
            message.value = 'Pola jadwal dan sesi pertemuannya berhasil dibuat.';
        }
        resetForm();
        await load();
    } catch (requestError) {
        error.value = getErrorMessage(requestError);
        conflicts.value = requestError.response?.data?.conflicts ?? [];
        suggestions.value = requestError.response?.data?.suggestions ?? [];
    } finally {
        saving.value = false;
    }
}

function applySuggestion(suggestion) {
    form.weekday = suggestion.weekday;
    form.starts_at = suggestion.starts_at;
    form.ends_at = suggestion.ends_at;
    form.room = suggestion.room;
    conflicts.value = [];
    suggestions.value = [];
    error.value = '';
}

async function deactivateSchedule(schedule) {
    if (!window.confirm(`Nonaktifkan jadwal ${weekdayName(schedule.weekday)} di ${schedule.room}?`)) return;

    try {
        await api.delete(`/course-schedules/${schedule.id}`);
        message.value = 'Jadwal dinonaktifkan. Sesi mendatang tanpa presensi dihapus; riwayat yang sudah tercatat dipertahankan.';
        await load();
    } catch (requestError) {
        error.value = getErrorMessage(requestError);
    }
}

async function showCode(session) {
    error.value = '';
    try {
        let cached = attendanceCodes[session.id];
        if (!cached || Date.now() > Date.parse(cached.valid_until)) {
            const response = await api.get(`/sessions/${session.id}/attendance-code`);
            const { code, valid_until: validUntil } = response.data.data;
            const attendanceUrl = new URL(`/courses/${course.value.id}/schedule`, window.location.origin);
            attendanceUrl.searchParams.set('session', session.id);
            attendanceUrl.searchParams.set('code', code);
            const image = await QRCode.toDataURL(attendanceUrl.toString(), { errorCorrectionLevel: 'M', margin: 1, width: 220 });
            cached = { code, image, valid_until: validUntil };
            attendanceCodes[session.id] = cached;
        }
        qr.value = { session, ...cached };
    } catch (requestError) {
        error.value = getErrorMessage(requestError);
    }
}

async function submitAttendance(session) {
    const code = (attendanceInputs[session.id] ?? '').trim();
    if (!code) {
        attendanceMessages[session.id] = 'Masukkan kode yang ditampilkan dosen.';
        return;
    }

    try {
        const source = qrSessionId.value === session.id ? 'qr' : 'code';
        const response = await api.post(`/sessions/${session.id}/attendance`, { code, source });
        attendanceMessages[session.id] = response.data.data.message;
        attendanceInputs[session.id] = '';
        qrSessionId.value = null;
        if (route.query.session) await router.replace({ query: {} });
        await load();
    } catch (requestError) {
        attendanceMessages[session.id] = getErrorMessage(requestError);
    }
}

async function initialize() {
    await load();
    const sessionId = Number(route.query.session);
    const code = typeof route.query.code === 'string' ? route.query.code : '';
    const session = sessions.value.find((item) => item.id === sessionId);

    if (session && code && !canManage.value && !session.attendance_recorded) {
        qrSessionId.value = sessionId;
        attendanceInputs[sessionId] = code;
        attendanceMessages[sessionId] = 'QR siap. Tekan Hadir untuk mengonfirmasi presensi.';
    }
}

onMounted(initialize);
</script>

<template>
    <PageFrame
        :title="course ? `Jadwal & presensi — ${course.name}` : 'Jadwal & presensi'"
        :subtitle="course ? `${course.code} · Waktu kampus Asia/Makassar (WITA)` : ''"
        :loading="loading"
        :error="error"
    >
        <template #actions>
            <RouterLink v-if="course" class="button button-quiet" :to="`/courses/${course.id}`">Kembali ke mata kuliah</RouterLink>
        </template>

        <p v-if="message" class="notice" role="status">{{ message }}</p>

        <section v-if="canManage" class="panel">
            <h2>{{ editingScheduleId ? 'Ubah pola jadwal' : 'Tambah pola jadwal mingguan' }}</h2>
            <form class="schedule-form" @submit.prevent="saveSchedule">
                <label>Hari
                    <select v-model.number="form.weekday" required>
                        <option v-for="day in weekdays" :key="day.value" :value="day.value">{{ day.label }}</option>
                    </select>
                </label>
                <label>Mulai <input v-model="form.starts_at" type="time" required></label>
                <label>Selesai <input v-model="form.ends_at" type="time" required></label>
                <label>Ruang <input v-model="form.room" maxlength="100" required placeholder="Contoh: Lab A-203"></label>
                <label>Berlaku dari <input v-model="form.effective_from" type="date" required></label>
                <label>Berlaku sampai <input v-model="form.effective_until" type="date" required></label>
                <div class="actions">
                    <button class="button" type="submit" :disabled="saving">{{ saving ? 'Menyimpan…' : 'Simpan jadwal' }}</button>
                    <button v-if="editingScheduleId" class="button button-quiet" type="button" @click="resetForm">Batal ubah</button>
                </div>
            </form>

            <div v-if="conflicts.length" class="conflict-box" role="alert">
                <h3>Jadwal tidak dapat disimpan</h3>
                <p v-for="(conflict, index) in conflicts" :key="`${conflict.course_id}-${index}`">
                    Bentrok dengan <strong>{{ conflict.course_code }} — {{ conflict.course_name }}</strong>
                    pada {{ conflict.weekday_name }}, {{ conflict.starts_at }}–{{ conflict.ends_at }} di {{ conflict.room }}:
                    {{ conflict.reasons.join(' dan ') }}.
                </p>
                <h4 v-if="suggestions.length">Saran slot alternatif</h4>
                <ul v-if="suggestions.length" class="suggestions">
                    <li v-for="slot in suggestions" :key="`${slot.weekday}-${slot.starts_at}`">
                        <button class="text-button" type="button" @click="applySuggestion(slot)">
                            {{ slot.weekday_name }} · {{ slot.starts_at }}–{{ slot.ends_at }} · {{ slot.room }}
                        </button>
                    </li>
                </ul>
                <p v-else>Belum ditemukan slot alternatif otomatis. Ubah hari, waktu, atau ruang lalu coba lagi.</p>
            </div>
        </section>

        <section class="panel">
            <h2>Pola jadwal aktif</h2>
            <ul v-if="schedules.length" class="schedule-list">
                <li v-for="schedule in schedules" :key="schedule.id">
                    <div>
                        <strong>{{ weekdayName(schedule.weekday) }} · {{ schedule.starts_at }}–{{ schedule.ends_at }}</strong>
                        <span>{{ schedule.room }} · {{ schedule.effective_from }} sampai {{ schedule.effective_until }}</span>
                    </div>
                    <div v-if="canManage" class="actions">
                        <button class="button button-quiet" type="button" @click="editSchedule(schedule)">Ubah</button>
                        <button class="button button-danger" type="button" @click="deactivateSchedule(schedule)">Nonaktifkan</button>
                    </div>
                </li>
            </ul>
            <p v-else>Belum ada pola jadwal aktif.</p>
        </section>

        <section class="panel">
            <h2>Sesi kuliah mendatang</h2>
            <p class="muted">Daftar ini menampilkan sesi, bukan rekap kehadiran per sesi.</p>
            <ul v-if="sessions.length" class="session-list">
                <li v-for="session in sessions" :key="session.id">
                    <div class="session-details">
                        <strong>{{ formatSessionTime(session.starts_at) }}–{{ formatSessionTime(session.ends_at) }}</strong>
                        <span>{{ session.room }}</span>
                    </div>
                    <div v-if="canManage" class="actions">
                        <button class="button button-quiet" type="button" @click="showCode(session)">Tampilkan QR/kode</button>
                    </div>
                    <form v-else-if="!session.attendance_recorded" class="checkin-form" @submit.prevent="submitAttendance(session)">
                        <label :for="`attendance-code-${session.id}`">{{ qrSessionId === session.id ? 'Kode dari QR' : 'Kode presensi' }}</label>
                        <input :id="`attendance-code-${session.id}`" v-model="attendanceInputs[session.id]" autocomplete="one-time-code" maxlength="64">
                        <button class="button" type="submit">{{ qrSessionId === session.id ? 'Konfirmasi hadir' : 'Hadir' }}</button>
                        <span v-if="attendanceMessages[session.id]" role="status">{{ attendanceMessages[session.id] }}</span>
                    </form>
                    <span v-else class="success" role="status">Presensi Anda tercatat</span>
                </li>
            </ul>
            <p v-else>Tidak ada sesi dalam 30 hari ke depan.</p>
        </section>

        <div v-if="qr" class="modal-backdrop" @click.self="qr = null">
            <section class="qr-card" role="dialog" aria-modal="true" aria-label="Kode presensi">
                <button class="close-button" type="button" aria-label="Tutup" @click="qr = null">×</button>
                <h2>Kode presensi</h2>
                <p>{{ formatSessionTime(qr.session.starts_at) }} · {{ qr.session.room }}</p>
                <img :src="qr.image" alt="QR code presensi sesi">
                <code>{{ qr.code }}</code>
                <p class="muted">Gunakan QR/kode ini selama sesi berlangsung. Kode yang sama dipakai bersama seluruh mahasiswa.</p>
            </section>
        </div>
    </PageFrame>
</template>

<style scoped>
.panel { margin-bottom: 16px; padding: 22px; border: 1px solid #e7e4ee; border-radius: 14px; background: #fff; }
.panel h2 { margin-top: 0; }
.schedule-form { display: grid; grid-template-columns: repeat(auto-fit, minmax(170px, 1fr)); gap: 12px; align-items: end; }
label { display: grid; gap: 6px; font-size: .88rem; font-weight: 600; }
input, select { min-width: 0; padding: 9px 10px; border: 1px solid #d5d9e3; border-radius: 8px; background: #fff; }
.actions { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.schedule-list, .session-list { display: grid; gap: 10px; padding: 0; list-style: none; }
.schedule-list li, .session-list li { display: flex; flex-wrap: wrap; justify-content: space-between; gap: 12px; align-items: center; padding: 12px; border: 1px solid #eceaf1; border-radius: 10px; }
.schedule-list li > div:first-child, .session-details { display: grid; gap: 4px; }
.schedule-list span, .session-details span, .muted { color: #64748b; font-size: .88rem; }
.checkin-form { display: flex; flex-wrap: wrap; gap: 8px; align-items: center; }
.checkin-form label { display: block; }
.checkin-form input { width: 150px; }
.checkin-form span { flex-basis: 100%; color: #475569; }
.success { color: #167447; font-weight: 600; }
.notice { margin-bottom: 14px; padding: 12px; border-radius: 8px; background: #eaf8ef; color: #17613a; }
.conflict-box { margin-top: 16px; padding: 14px; border-radius: 9px; background: #fff3f1; color: #7f1d1d; }
.conflict-box h3, .conflict-box h4 { margin: 0 0 8px; }
.suggestions { margin: 0; padding-left: 18px; }
.text-button { padding: 4px; border: 0; background: transparent; color: #2456b8; cursor: pointer; text-decoration: underline; }
.modal-backdrop { position: fixed; inset: 0; z-index: 200; display: grid; place-items: center; padding: 16px; background: rgba(15, 23, 42, .55); }
.qr-card { position: relative; width: min(100%, 360px); padding: 26px; border-radius: 16px; background: #fff; text-align: center; box-shadow: 0 18px 50px rgba(0, 0, 0, .2); }
.qr-card img { display: block; width: 220px; max-width: 100%; height: auto; margin: 12px auto; }
.qr-card code { display: block; overflow-wrap: anywhere; font-size: 1.15rem; font-weight: 700; letter-spacing: .08em; }
.close-button { position: absolute; top: 8px; right: 12px; border: 0; background: transparent; font-size: 1.5rem; cursor: pointer; }
@media (max-width: 560px) { .session-list li { align-items: stretch; } .checkin-form { width: 100%; } .checkin-form input { flex: 1; } }
</style>

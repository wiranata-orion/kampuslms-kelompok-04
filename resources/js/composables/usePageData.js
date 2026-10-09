import { computed, onMounted, reactive, ref, watch } from 'vue';
import { useRoute, useRouter } from 'vue-router';
import api, { getErrorMessage, setToken } from '../api';

export function usePageData(page) {
    const route = useRoute();
    const router = useRouter();
    const user = ref(null);
    const data = ref(null);
    const rows = ref([]);
    const lecturers = ref([]);
    const candidates = ref([]);
    const loading = ref(false);
    const error = ref('');
    const search = ref('');
    const status = ref('');
    const role = ref('');
    const currentPage = ref(1);
    const pagination = ref(null);
    const form = reactive({});
    const mode = computed(() => route.meta.mode);

    const fields = computed(() => {
        const configs = {
            login: [
                { name: 'email', label: 'Email', type: 'email', required: true },
                { name: 'password', label: 'Kata sandi', type: 'password', required: true },
            ],
            course: [
                { name: 'code', label: 'Kode mata kuliah', required: true },
                { name: 'name', label: 'Nama mata kuliah', required: true },
                { name: 'description', label: 'Deskripsi', type: 'textarea' },
                { name: 'sks', label: 'SKS', type: 'number', min: 1, max: 6, required: true },
                { name: 'lecturer_id', label: 'Dosen pengampu', type: 'select', required: true, options: lecturers.value.map((item) => ({ value: item.id, text: item.name })) },
                { name: 'status', label: 'Status', type: 'select', options: ['draft', 'active', 'archived'].map((item) => ({ value: item, text: item })) },
            ],
            assignment: [
                { name: 'title', label: 'Judul tugas', required: true },
                { name: 'instructions', label: 'Instruksi', type: 'textarea', required: true },
                { name: 'due_at', label: 'Batas pengumpulan', type: 'datetime-local', required: true },
                { name: 'max_score', label: 'Nilai maksimal', type: 'number', min: 1, max: 100, required: true },
                { name: 'status', label: 'Publikasi', type: 'select', options: ['draft', 'published'].map((item) => ({ value: item, text: item })) },
                { name: 'allow_late', label: 'Izinkan terlambat', type: 'checkbox' },
            ],
            material: [
                { name: 'title', label: 'Judul materi', required: true },
                { name: 'description', label: 'Deskripsi', type: 'textarea' },
                { name: 'type', label: 'Jenis materi', type: 'select', options: [{ value: 'file', text: 'File' }, { value: 'link', text: 'Tautan' }] },
                { name: 'external_url', label: 'URL eksternal', type: 'url' },
                { name: 'file', label: 'Berkas', type: 'file' },
            ],
            user: [
                { name: 'name', label: 'Nama lengkap', required: true },
                { name: 'email', label: 'Email', type: 'email', required: true },
                ...(mode.value === 'create' ? [{ name: 'password', label: 'Kata sandi', type: 'password', required: true }] : []),
                { name: 'role', label: 'Peran', type: 'select', required: true, options: ['admin', 'dosen', 'mahasiswa'].map((item) => ({ value: item, text: item })) },
                { name: 'nim_nip', label: 'NIM / NIP' },
            ],
            grade: [
                { name: 'score', label: 'Nilai', type: 'number', min: 0, max: data.value?.assignment?.max_score, step: '0.01', required: true },
                { name: 'feedback', label: 'Umpan balik', type: 'textarea' },
            ],
            submission: [
                { name: 'note', label: 'Catatan untuk dosen', type: 'textarea' },
                { name: 'file', label: 'Ganti file', type: 'file' },
            ],
            enrollment: [
                { name: 'user_id', label: 'Mahasiswa', type: 'select', required: true, options: candidates.value.map((item) => ({ value: item.id, text: `${item.name} (${item.nim_nip ?? item.email})` })) },
            ],
        };
        const kind = {
            'login': 'login',
            'course-form': 'course',
            'assignment-form': 'assignment',
            'material-form': 'material',
            'user-form': 'user',
            'grade-form': 'grade',
            'grade': 'grade',
            'submission-form': 'submission',
            'enrollment-form': 'enrollment',
        }[page];
        return configs[kind] ?? [];
    });

    function clearForm() {
        for (const key of Object.keys(form)) delete form[key];
        for (const field of fields.value) form[field.name] = field.type === 'checkbox' ? false : '';
        if ('max_score' in form) form.max_score = 100;
        if ('status' in form) form.status = page === 'assignment-form' || page === 'course-form' ? 'draft' : 'active';
        if ('type' in form) form.type = 'file';
    }

    function array(response) {
        return response?.data?.data ?? [];
    }

    async function load() {
        loading.value = true;
        error.value = '';
        rows.value = [];
        data.value = null;
        pagination.value = null;
        candidates.value = [];
        try {
            if (['about', 'welcome', 'forbidden', 'not-found', 'login'].includes(page)) return;
            user.value = (await api.get('/me')).data.data;
            const id = route.params.id;
            let response;
            switch (page) {
                case 'dashboard':
                    data.value = (await api.get('/dashboard')).data.data;
                    user.value = data.value.user;
                    break;
                case 'my-courses':
                    response = await api.get('/my/courses', { params: { page: currentPage.value } });
                    rows.value = array(response);
                    pagination.value = response.data.meta;
                    break;
                case 'courses':
                    response = await api.get('/courses', { params: { scope: 'all', search: search.value || undefined, status: status.value || undefined, page: currentPage.value } });
                    rows.value = array(response);
                    pagination.value = response.data.meta;
                    break;
                case 'course':
                    data.value = (await api.get(`/courses/${id}`)).data.data;
                    data.value.assignments = array(await api.get(`/courses/${id}/assignments`));
                    data.value.materials = array(await api.get(`/courses/${id}/materials`));
                    if (user.value.role === 'admin') {
                        const [students, enrolled] = await Promise.all([
                            api.get(`/courses/${id}/students`),
                            api.get(`/courses/${id}/enrollments/candidates`),
                        ]);
                        data.value.students = array(students);
                        candidates.value = enrolled.data.data.candidates;
                    }
                    break;
                case 'course-assignments':
                    data.value = (await api.get(`/courses/${id}`)).data.data;
                    response = await api.get(`/courses/${id}/assignments`, { params: { page: currentPage.value } });
                    rows.value = array(response);
                    pagination.value = response.data.meta;
                    break;
                case 'course-materials':
                    data.value = (await api.get(`/courses/${id}`)).data.data;
                    response = await api.get(`/courses/${id}/materials`, { params: { page: currentPage.value } });
                    rows.value = array(response);
                    pagination.value = response.data.meta;
                    break;
                case 'course-form':
                    lecturers.value = array(await api.get('/lecturers'));
                    if (mode.value === 'edit') data.value = (await api.get(`/courses/${id}`)).data.data;
                    clearForm();
                    if (data.value) Object.assign(form, data.value);
                    break;
                case 'assignment':
                    data.value = (await api.get(`/assignments/${id}`)).data.data;
                    if (user.value.role === 'dosen') data.value.submissions = array(await api.get(`/assignments/${id}/submissions`));
                    if (user.value.role === 'mahasiswa') {
                        const submissions = array(await api.get('/my/submissions'));
                        data.value.submission = submissions.find((item) => item.assignment_id === data.value.id);
                    }
                    break;
                case 'assignment-submissions':
                    data.value = (await api.get(`/assignments/${id}`)).data.data;
                    response = await api.get(`/assignments/${id}/submissions`, { params: { page: currentPage.value } });
                    rows.value = array(response);
                    pagination.value = response.data.meta;
                    break;
                case 'assignment-form':
                    data.value = mode.value === 'edit'
                        ? (await api.get(`/assignments/${id}`)).data.data
                        : { course: (await api.get(`/courses/${id}`)).data.data };
                    clearForm();
                    if (mode.value === 'edit') Object.assign(form, data.value, { due_at: data.value.due_at?.slice(0, 16) });
                    break;
                case 'material':
                    data.value = (await api.get(`/materials/${id}`)).data.data;
                    break;
                case 'material-form':
                    data.value = mode.value === 'edit'
                        ? (await api.get(`/materials/${id}`)).data.data
                        : { course: (await api.get(`/courses/${id}`)).data.data };
                    clearForm();
                    if (mode.value === 'edit') Object.assign(form, data.value);
                    break;
                case 'users':
                    response = await api.get('/users', { params: { search: search.value || undefined, role: role.value || undefined, page: currentPage.value } });
                    rows.value = array(response);
                    pagination.value = response.data.meta;
                    break;
                case 'user':
                    data.value = (await api.get(`/users/${id}`)).data.data;
                    break;
                case 'user-form':
                    if (mode.value === 'edit') data.value = (await api.get(`/users/${id}`)).data.data;
                    clearForm();
                    if (data.value) Object.assign(form, data.value);
                    break;
                case 'notifications':
                    response = await api.get('/notifications', { params: { page: currentPage.value } });
                    rows.value = array(response);
                    pagination.value = response.data.meta;
                    break;
                case 'my-submissions':
                    response = await api.get('/my/submissions', { params: { page: currentPage.value } });
                    rows.value = array(response);
                    pagination.value = response.data.meta;
                    break;
                case 'submission':
                case 'submission-form':
                case 'grade':
                    response = await api.get(route.meta.gradeById ? `/grades/${id}` : `/submissions/${id}`);
                    data.value = response.data.data;
                    if (page === 'submission-form') {
                        clearForm();
                        form.note = data.value.note ?? '';
                    } else if (page === 'grade' && user.value.role === 'dosen') {
                        clearForm();
                        if (data.value.grade) Object.assign(form, data.value.grade);
                    }
                    break;
                case 'enrollments':
                case 'enrollment-form': {
                    data.value = (await api.get(`/courses/${id}`)).data.data;
                    const [students, candidatesResponse] = await Promise.all([
                        api.get(`/courses/${id}/students`, { params: { page: currentPage.value } }),
                        api.get(`/courses/${id}/enrollments/candidates`),
                    ]);
                    data.value.students = array(students);
                    pagination.value = students.data.meta;
                    candidates.value = candidatesResponse.data.data.candidates;
                    if (page === 'enrollment-form') clearForm();
                    break;
                }
                default:
                    break;
            }
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
            if (requestError.response?.status === 403 && page !== 'forbidden') {
                await router.replace({ name: 'forbidden', query: { message: error.value } });
            } else if (requestError.response?.status === 401 && page !== 'login') {
                setToken(null);
                await router.replace({ name: 'login' });
            }
        } finally {
            loading.value = false;
        }
    }

    async function submit() {
        error.value = '';
        try {
            const id = route.params.id;
            if (page === 'grade' && user.value.role === 'dosen') {
                await api.put(`/submissions/${data.value.id}/grade`, form);
                await router.push(`/submissions/${data.value.id}`);
                return;
            }
            if (page === 'enrollment-form') {
                await api.post(`/courses/${id}/enrollments`, form);
                await router.push(`/courses/${id}/enrollments`);
                return;
            }
            if (page === 'submission-form') {
                const body = new FormData();
                body.append('note', form.note ?? '');
                if (form.file instanceof File) body.append('file', form.file);
                body.append('_method', 'PUT');
                await api.post(`/submissions/${id}`, body);
                await router.push(`/submissions/${id}`);
                return;
            }
            const editing = mode.value === 'edit';
            const payload = Object.fromEntries(fields.value.map(({ name }) => [name, form[name]]));
            const resource = {
                'course-form': { url: editing ? `/courses/${id}` : '/courses', back: editing ? `/courses/${id}` : '/courses' },
                'assignment-form': { url: editing ? `/assignments/${id}` : '/assignments', back: editing ? `/courses/${data.value.course_id}/assignments` : `/courses/${id}` },
                'material-form': { url: editing ? `/materials/${id}` : `/courses/${id}/materials`, back: editing ? `/courses/${data.value.course_id}/materials` : `/courses/${id}/materials` },
                'user-form': { url: editing ? `/users/${id}` : '/users', back: editing ? `/users/${id}` : '/users' },
            }[page];
            if (page === 'assignment-form' && !editing) payload.course_id = id;
            if (page === 'material-form') {
                const body = new FormData();
                Object.entries(payload).forEach(([key, value]) => {
                    if (value !== null && value !== undefined && value !== '') body.append(key, value);
                });
                if (editing) body.append('_method', 'PUT');
                await api.post(resource.url, body);
            } else if (editing) {
                await api.put(resource.url, payload);
            } else {
                await api.post(resource.url, payload);
            }
            await router.push(resource.back);
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
        }
    }

    async function login() {
        try {
            const response = await api.post('/auth/login', { ...form, device_name: 'kampuslms-web' });
            setToken(response.data.data.token);
            user.value = response.data.data.user;
            await router.push('/dashboard');
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
        }
    }

    async function remove(kind, item) {
        if (!window.confirm(`Hapus ${kind} ini?`)) return;
        try {
            await api.delete(`/${kind}/${item.id}`);
            if (kind === 'courses') return router.push('/courses');
            if (kind === 'users' && page === 'user') return router.push('/users');
            if (kind === 'assignments') return router.push(`/courses/${data.value.course_id}/assignments`);
            if (kind === 'materials') return router.push(`/courses/${data.value.course_id}/materials`);
            await load();
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
        }
    }

    async function enroll(userId) {
        try {
            await api.post(`/courses/${route.params.id}/enrollments`, { user_id: userId });
            await load();
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
        }
    }

    async function removeStudent(student) {
        try {
            await api.delete(`/courses/${route.params.id}/enrollments/${student.id}`);
            await load();
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
        }
    }

    async function readNotification(notification) {
        try {
            await api.post(`/notifications/${notification.id}/read`);
            await load();
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
        }
    }

    async function readAllNotifications() {
        try {
            await api.post('/notifications/read-all');
            await load();
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
        }
    }

    async function download(url, filename) {
        try {
            const response = await api.get(url, { responseType: 'blob' });
            const objectUrl = URL.createObjectURL(response.data);
            const anchor = document.createElement('a');
            anchor.href = objectUrl;
            anchor.download = filename;
            anchor.click();
            URL.revokeObjectURL(objectUrl);
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
        }
    }

    async function submitAssignment(file, note) {
        if (!file) {
            error.value = 'Pilih berkas pengumpulan terlebih dahulu.';
            return;
        }
        const body = new FormData();
        body.append('file', file);
        body.append('note', note ?? '');
        try {
            const response = await api.post(`/assignments/${data.value.id}/submissions`, body);
            await router.push(`/submissions/${response.data.data.id}`);
        } catch (requestError) {
            error.value = getErrorMessage(requestError);
        }
    }

    function goToPage(pageNumber) {
        currentPage.value = pageNumber;
        load();
    }

    function setFile(event) {
        form.file = event.target.files?.[0] ?? null;
    }

    watch(() => route.fullPath, () => {
        currentPage.value = 1;
        load();
    });
    onMounted(load);

    return {
        user, data, rows, lecturers, candidates, loading, error, search, status, role,
        currentPage, pagination, form, fields, mode, router, load, submit, login,
        remove, enroll, removeStudent, readNotification, readAllNotifications,
        download, submitAssignment, goToPage, setFile,
    };
}

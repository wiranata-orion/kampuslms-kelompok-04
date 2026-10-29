import { createRouter, createWebHistory } from 'vue-router';
import About from './Pages/About.vue';
import Welcome from './Pages/Welcome.vue';
import Login from './Pages/Auth/Login.vue';
import ForgotPassword from './Pages/Auth/ForgotPassword.vue';
import Dashboard from './Pages/Dashboard/Index.vue';
import CourseIndex from './Pages/Courses/Index.vue';
import MyCourses from './Pages/Courses/Mine.vue';
import CourseShow from './Pages/Courses/Show.vue';
import CourseForm from './Pages/Courses/Form.vue';
import CourseAssignments from './Pages/Assignments/Index.vue';
import AssignmentShow from './Pages/Assignments/Show.vue';
import AssignmentForm from './Pages/Assignments/Form.vue';
import AssignmentSubmissions from './Pages/Assignments/Submissions.vue';
import CourseMaterials from './Pages/Materials/Index.vue';
import MaterialShow from './Pages/Materials/Show.vue';
import MaterialForm from './Pages/Materials/Form.vue';
import EnrollmentIndex from './Pages/Courses/Enrollments.vue';
import EnrollmentForm from './Pages/Courses/EnrollmentForm.vue';
import UserIndex from './Pages/Users/Index.vue';
import UserShow from './Pages/Users/Show.vue';
import UserForm from './Pages/Users/Form.vue';
import Notifications from './Pages/Notifications/Index.vue';
import SubmissionIndex from './Pages/Submissions/Index.vue';
import SubmissionShow from './Pages/Submissions/Show.vue';
import SubmissionForm from './Pages/Submissions/Form.vue';
import GradeShow from './Pages/Grades/Show.vue';
import Forbidden from './Pages/Errors/Forbidden.vue';
import NotFound from './Pages/Errors/NotFound.vue';

const router = createRouter({
    history: createWebHistory(),
    routes: [
        { path: '/about', name: 'about', component: About, meta: { public: true } },
        { path: '/tentang', redirect: '/about' },
        { path: '/welcome', name: 'welcome', component: Welcome, meta: { public: true } },
        { path: '/403', name: 'forbidden', component: Forbidden, meta: { public: true } },
        { path: '/login', name: 'login', component: Login, meta: { guest: true } },
        { path: '/forgot-password', name: 'forgot-password', component: ForgotPassword, meta: { public: true } },
        { path: '/', redirect: '/dashboard' },
        { path: '/dashboard', name: 'dashboard', component: Dashboard },
        { path: '/my/courses', alias: ['/dosen/courses', '/mahasiswa/courses'], name: 'my-courses', component: MyCourses },
        { path: '/courses', alias: '/admin/courses', name: 'courses', component: CourseIndex },
        { path: '/courses/create', alias: '/admin/courses/create', name: 'course-create', component: CourseForm, meta: { mode: 'create' } },
        { path: '/courses/:id/edit', alias: '/admin/courses/:id/edit', name: 'course-edit', component: CourseForm, meta: { mode: 'edit' } },
        { path: '/courses/:id/materials', alias: '/dosen/courses/:id/materials', name: 'course-materials', component: CourseMaterials },
        { path: '/courses/:id/materials/create', alias: '/dosen/courses/:id/materials/create', name: 'material-create', component: MaterialForm, meta: { mode: 'create' } },
        { path: '/courses/:id/enrollments', alias: '/admin/courses/:id/enrollments', name: 'course-enrollments', component: EnrollmentIndex },
        { path: '/courses/:id/enrollments/create', alias: '/admin/courses/:id/enrollments/create', name: 'enrollment-create', component: EnrollmentForm },
        { path: '/courses/:id', name: 'course', component: CourseShow },
        { path: '/courses/:id/assignments', alias: '/dosen/courses/:id/assignments', name: 'course-assignments', component: CourseAssignments },
        { path: '/courses/:id/assignments/create', alias: '/dosen/courses/:id/assignments/create', name: 'assignment-create', component: AssignmentForm, meta: { mode: 'create' } },
        { path: '/assignments/:id/submissions', alias: '/dosen/assignments/:id/submissions', name: 'assignment-submissions', component: AssignmentSubmissions },
        { path: '/assignments/:id', alias: ['/dosen/assignments/:id', '/mahasiswa/assignments/:id/submissions/create'], name: 'assignment', component: AssignmentShow },
        { path: '/assignments/:id/edit', alias: '/dosen/assignments/:id/edit', name: 'assignment-edit', component: AssignmentForm, meta: { mode: 'edit' } },
        { path: '/users', alias: '/admin/users', name: 'users', component: UserIndex, meta: { admin: true } },
        { path: '/users/create', alias: '/admin/users/create', name: 'user-create', component: UserForm, meta: { mode: 'create', admin: true } },
        { path: '/users/:id', alias: '/admin/users/:id', name: 'user', component: UserShow, meta: { admin: true } },
        { path: '/users/:id/edit', alias: '/admin/users/:id/edit', name: 'user-edit', component: UserForm, meta: { mode: 'edit', admin: true } },
        { path: '/materials/:id', alias: '/dosen/materials/:id', name: 'material', component: MaterialShow },
        { path: '/materials/:id/edit', alias: '/dosen/materials/:id/edit', name: 'material-edit', component: MaterialForm, meta: { mode: 'edit' } },
        { path: '/notifications', alias: '/notifications/read-all', name: 'notifications', component: Notifications },
        { path: '/my/submissions', name: 'my-submissions', component: SubmissionIndex },
        { path: '/submissions/:id', alias: ['/dosen/submissions/:id', '/mahasiswa/submissions/:id'], name: 'submission', component: SubmissionShow },
        { path: '/submissions/:id/edit', alias: '/mahasiswa/submissions/:id/edit', name: 'submission-edit', component: SubmissionForm },
        { path: '/submissions/:id/grade', alias: '/mahasiswa/submissions/:id/grade', name: 'grade', component: GradeShow },
        { path: '/dosen/submissions/:id/grade/create', name: 'grade-create-legacy', component: GradeShow },
        { path: '/dosen/grades/:id/edit', name: 'grade-edit-legacy', component: GradeShow, meta: { gradeById: true } },
        { path: '/:pathMatch(.*)*', name: 'not-found', component: NotFound, meta: { public: true } },
    ],
});

router.beforeEach((to) => {
    const hasToken = Boolean(localStorage.getItem('kampuslms_token'));
    if (!to.meta.guest && !to.meta.public && !hasToken) return { name: 'login' };
    if (to.meta.guest && hasToken) return { name: 'dashboard' };
    return true;
});

export default router;

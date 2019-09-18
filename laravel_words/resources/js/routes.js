import Home from './components/Home';
import About from './components/About';
import Exam from './components/Exam';
import Search from './components/Search';
import Study from './components/Study';
import StudyResult from './components/StudyResult';
import Words from './components/Words';
import Edit from './components/Edit';
import NotFound from './components/NotFound';

export default {
    linkActiveClass: 'font-bold',
    mode: 'history',
    scrollBehavior(to, from, savedPosition) {
        const selector = location.hash
        return selector ? { selector } : { x: 0, y: 0 }
    },
    routes: [{
            path: '/',
            component: Home
        },
        {
            path: '/about',
            component: About
        },
        {
            path: '/exam',
            name: 'exam',
            component: Exam
        },
        {
            path: '/search',
            component: Search,
            meta: { requiresAuth: true }
        },
        {
            path: '/study',
            component: Study,
            meta: { requiresAuth: true }

        },
        {
            path: '/study/result',
            component: StudyResult,
            meta: { requiresAuth: true }

        },
        {
            path: '/words',
            name: 'words',
            component: Words,
            meta: { requiresAuth: true }
        },
        {
            path: '/edit/:id+',
            name: 'edit',
            component: Edit,
            props: true,
            meta: { requiresAuth: true }
        },
        {
            path: '*',
            component: NotFound,
        },
    ],
}

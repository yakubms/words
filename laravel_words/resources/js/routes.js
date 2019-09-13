import Home from './components/Home';
import About from './components/About';
import Exam from './components/Exam';
import Search from './components/Search';
import Study from './components/Study';
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
            component: Search
        },
        {
            path: '/study',
            component: Study
        },
        {
            path: '/words',
            name: 'words',
            component: Words
        },
        {
            path: '/edit/:id+',
            name: 'edit',
            component: Edit,
            props: true
        },
        {
            path: '*',
            component: NotFound,
        },
    ]
}

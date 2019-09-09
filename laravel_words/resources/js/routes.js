import Home from './components/Home';
import About from './components/About';
import Exam from './components/Exam';
import Study from './components/Study';
import Words from './components/Words';

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
            path: '/words',
            name: 'words',
            component: Words
        },
        {
            path: '/study',
            component: Study
        },
    ]
}

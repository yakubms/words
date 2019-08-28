import Home from './components/Home';
import About from './components/About';
import Exam from './components/Exam';
import Study from './components/Study';
import Words from './components/Words';

export default {
    linkActiveClass: 'font-bold',
    mode: 'history',
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
            component: Exam
        },
        {
            path: '/words',
            component: Words
        },
        {
            path: '/study',
            component: Study
        },
    ]
}

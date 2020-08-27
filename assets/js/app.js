import Vue from 'vue';
import CheeseWhizApp from './components/CheeseWhizApp';
import 'bootstrap/dist/css/bootstrap.css';

new Vue({
    render(h) {
        return h(CheeseWhizApp);
    },
}).$mount('#cheese-app');

import Vue from 'vue';
import CheeseWhizApp from './components/CheeseWhizApp';
import 'bootstrap/dist/css/bootstrap.css';

Vue.component('cheese-whiz-app', CheeseWhizApp);

const app = new Vue({
    el: '#cheese-app'
});

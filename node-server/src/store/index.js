import Vuex from 'vuex';
import global from './modules/global';
import zaful from './modules/zaful';
import rosegal from './modules/rosegal';
import dresslily from './modules/dresslily';
import page from '../../../htdocs/src/store/modules/page';
import jetlore from './modules/jetlore';
import growingio from './modules/growing-io';

const { Vue } = window;
Vue.use(Vuex);

const store = new Vuex.Store({
    modules: {
        page,
        global,
        zaful,
        rosegal,
        dresslily,
        jetlore,
        growingio
    }
});

export default store;

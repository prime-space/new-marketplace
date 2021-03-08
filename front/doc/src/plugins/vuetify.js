import Vue from 'vue'
import Vuetify from 'vuetify/lib'
import 'vuetify/dist/vuetify.min.css'

Vue.use(Vuetify);

export default new Vuetify({
  theme: {
    themes: {
      light: {
        primary: '#0177fd',
        accent: '#45aaf2',
      },
    }
  },
  icons: {
    iconfont: 'mdi',
  },
});

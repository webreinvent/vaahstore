import { createRouter,  createWebHashHistory  } from 'vue-router'
import qs from 'qs';

import routes from "./routes";
import Default from "../layouts/Default.vue";
import SettingsLayout from "../layouts/SettingLayout.vue";
import settingRoutes from "../routes/vue-routes-settings"


const router = createRouter({
  history: createWebHashHistory(),
  routes: [
      {
          path: '/',
          component: Default,
          props: true,
          children: routes
      },
      {
          path: '/settings',
          component: SettingsLayout,
          props: true,
          children:settingRoutes
      }
  ],
    parseQuery(query) {
        return qs.parse(query);
    },
    stringifyQuery(query) {
        let result = qs.stringify(query,
            {
                arrayFormat: 'brackets',
                encode: false,
                skipNulls: true
            });
        //return result ? ('?' + result) : '';
        return result;
    }
})

export default router

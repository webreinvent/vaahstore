import { createRouter,  createWebHashHistory  } from 'vue-router'
import qs from 'qs';

import routes from "./routes";
import Default from "../layouts/Default.vue";
import settingRoutes from "../routes/vue-routes-settings"
import {useRootStore} from "../stores/root";

const router = createRouter({
  history: createWebHashHistory(),
  routes: [
      {
          path: '/',
          component: Default,
          props: true,
          children: routes
      },
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


router.beforeEach((to, from, next) => {
    const root = useRootStore();
    const selectedStore = root.selected_store_at_sidebar;
    const defaultStore = root.default_store;
    const queryStoreId = to.query.selected_store;
    // Prevent infinite redirects by only redirecting if needed
    if (selectedStore && !queryStoreId) {
        const store_id = selectedStore.id === defaultStore?.id ? defaultStore?.id : selectedStore?.id;

        // Check if the store_id is already the same as in the query
        if (queryStoreId !== String(store_id)) {
            next({
                ...to,
                query: {
                    ...to.query,
                    selected_store: store_id
                }
            });
        } else {
            next(); // Allow navigation if the query is already set correctly
        }

    } else {
        next(); // Proceed normally if the condition is not met
    }
});


export default router

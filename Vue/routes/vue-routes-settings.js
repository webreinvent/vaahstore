let routes= [];
let routes_list= [];

import SettingsLayout from "../pages/settings/SettingsLayout.vue";
import GeneralIndex from "../pages/settings/general/Index.vue";


routes_list = {
    path: '/settings',
    name: 'settings.index',
    component: SettingsLayout,
    props: true,
    children: [
        {
            path: 'general',
            name: 'settings.general',
            component: GeneralIndex,
            props: true,
        }

    ]
};

routes.push(routes_list);

export default routes;


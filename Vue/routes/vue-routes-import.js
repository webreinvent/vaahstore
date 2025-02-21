let routes= [];
let routes_list= [];

import Index from "../pages/import/Index.vue";
import Upload from "../pages/import/components/Upload.vue";
import Map from "../pages/import/components/Map.vue";
import Preview from "../pages/import/components/Preview.vue";
import Result from "../pages/import/components/Result.vue";
import Export from "../pages/import/components/Export.vue";


routes_list = {
    path: '/import',
    component: Index,
    props: true,
    name: 'import.index',
    children: [
        {
            path: 'upload',
            component: Upload,
            name: 'import.upload',
        },
        {
            path: 'map',
            component: Map,
            name: 'import.map',
        },
        {
            path: 'preview',
            component: Preview,
            name: 'import.preview',
        },
        {
            path: 'result',
            component: Result,
            name: 'import.result',
        },
        {
            path: 'export',
            component: Export,
            name: 'import.export',
        }
    ]

};

routes.push(routes_list);

export default routes;


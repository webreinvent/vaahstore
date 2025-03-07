let routes= [];
let routes_list= [];

import List from '../pages/productvariations/List.vue'
import Form from '../pages/productvariations/Form.vue'
import Item from '../pages/productvariations/Item.vue'
import Filters from "../pages/productvariations/Filters.vue";

routes_list = {

    path: '/productvariations',
    name: 'productvariations.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'productvariations.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'productvariations.view',
            component: Item,
            props: true,
        },
        {
            path: 'filters',
            name: 'productvariations.filters',
            component: Filters,
            props: true,
        },
    ]
};

routes.push(routes_list);

export default routes;


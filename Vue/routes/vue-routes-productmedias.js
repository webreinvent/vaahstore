let routes= [];
let routes_list= [];

import List from '../pages/productmedias/List.vue'
import Form from '../pages/productmedias/Form.vue'
import Item from '../pages/productmedias/Item.vue'
import Filters from "../pages/productmedias/Filters.vue";

routes_list = {

    path: '/productmedias',
    name: 'productmedias.index',
    component: List,
    props: true,
    children:[
        {
            path: 'filters',
            name: 'productmedias.filters',
            component: Filters,
            props: true,
        },
        {
            path: 'form/:id?',
            name: 'productmedias.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'productmedias.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


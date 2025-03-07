let routes= [];
let routes_list= [];

import List from '../pages/attributegroups/List.vue'
import Form from '../pages/attributegroups/Form.vue'
import Item from '../pages/attributegroups/Item.vue'
import Filters from "../pages/attributegroups/Filters.vue"

routes_list = {

    path: '/attributesgroup',
    name: 'attributegroups.index',
    component: List,
    props: true,
    children:[
        {
            path: 'filters',
            name: 'attributegroups.filters',
            component: Filters,
            props: true,
        },
        {
            path: 'form/:id?',
            name: 'attributegroups.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'attributegroups.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


let routes= [];
let routes_list= [];

import List from '../pages/attributes/List.vue'
import Form from '../pages/attributes/Form.vue'
import Item from '../pages/attributes/Item.vue'
import Filters from '../pages/attributes/Filters.vue'

routes_list = {

    path: '/attributes',
    name: 'attributes.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'attributes.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'attributes.view',
            component: Item,
            props: true,
        },
        {
            path: 'filters',
            name: 'attributes.filters',
            component: Filters,
            props: true,
        },
    ]
};

routes.push(routes_list);

export default routes;


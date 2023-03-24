let routes= [];
let routes_list= [];

import List from '../pages/attributegroups/List.vue'
import Form from '../pages/attributegroups/Form.vue'
import Item from '../pages/attributegroups/Item.vue'

routes_list = {

    path: '/attributegroups',
    name: 'attributegroups.index',
    component: List,
    props: true,
    children:[
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


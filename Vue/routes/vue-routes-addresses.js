let routes= [];
let routes_list= [];

import List from '../pages/addresses/List.vue'
import Form from '../pages/addresses/Form.vue'
import Item from '../pages/addresses/Item.vue'

routes_list = {

    path: '/addresses',
    name: 'addresses.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'addresses.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'addresses.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


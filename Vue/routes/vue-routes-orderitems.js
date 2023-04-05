let routes= [];
let routes_list= [];

import List from '../pages/orderitems/List.vue'
import Form from '../pages/orderitems/Form.vue'
import Item from '../pages/orderitems/Item.vue'

routes_list = {

    path: '/orderitems',
    name: 'orderitems.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'orderitems.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'orderitems.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


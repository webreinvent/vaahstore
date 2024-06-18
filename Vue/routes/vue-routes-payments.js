let routes= [];
let routes_list= [];

import List from '../pages/payments/List.vue'
import Form from '../pages/payments/Form.vue'
import Item from '../pages/payments/Item.vue'

routes_list = {

    path: '/payments',
    name: 'payments.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'payments.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'payments.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


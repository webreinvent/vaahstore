let routes= [];
let routes_list= [];

import List from '../pages/paymentmethods/List.vue'
import Form from '../pages/paymentmethods/Form.vue'
import Item from '../pages/paymentmethods/Item.vue'

routes_list = {

    path: '/paymentmethods',
    name: 'paymentmethods.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'paymentmethods.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'paymentmethods.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


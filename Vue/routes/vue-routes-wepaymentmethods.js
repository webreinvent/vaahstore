let routes= [];
let routes_list= [];

import List from '../pages/wepaymentmethods/List.vue'
import Form from '../pages/wepaymentmethods/Form.vue'
import Item from '../pages/wepaymentmethods/Item.vue'

routes_list = {

    path: '/wepaymentmethods',
    name: 'wepaymentmethods.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'wepaymentmethods.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'wepaymentmethods.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


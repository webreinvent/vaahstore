let routes= [];
let routes_list= [];

import List from '../pages/productprices/List.vue'
import Form from '../pages/productprices/Form.vue'
import Item from '../pages/productprices/Item.vue'

routes_list = {

    path: '/productprices',
    name: 'productprices.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'productprices.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'productprices.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


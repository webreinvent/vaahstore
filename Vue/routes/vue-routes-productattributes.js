let routes= [];
let routes_list= [];

import List from '../pages/productattributes/List.vue'
import Form from '../pages/productattributes/Form.vue'
import Item from '../pages/productattributes/Item.vue'

routes_list = {

    path: '/productattributes',
    name: 'productattributes.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'productattributes.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'productattributes.view',
            component: Item,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


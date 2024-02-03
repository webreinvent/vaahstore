let routes= [];
let routes_list= [];

import List from '../pages/products/List.vue'
import Form from '../pages/products/Form.vue'
import Item from '../pages/products/Item.vue'
import Variation from '../pages/products/Variation.vue'
import Vendor from '../pages/products/Vendor.vue'

routes_list = {

    path: '/products',
    name: 'products.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'products.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'products.view',
            component: Item,
            props: true,
        },
        {
            path: 'variation/:id?',
            name: 'products.variation',
            component: Variation,
            props: true,
        },
        {
            path: 'vendor/:id?',
            name: 'products.vendor',
            component: Vendor,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


let routes= [];
let routes_list= [];

import List from '../pages/productvendors/List.vue'
import Form from '../pages/productvendors/Form.vue'
import Item from '../pages/productvendors/Item.vue'
import Price from '../pages/productvendors/ProductPrice.vue'

routes_list = {

    path: '/vendorproducts',
    name: 'productvendors.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'productvendors.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'productvendors.view',
            component: Item,
            props: true,
        },
        {
            path: 'product/price/:id?',
            name: 'productvendors.productprice',
            component: Price,
            props: true,
        }
    ]
};

routes.push(routes_list);

export default routes;


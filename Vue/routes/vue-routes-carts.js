let routes= [];
let routes_list= [];

import List from '../pages/carts/List.vue'
import Form from '../pages/carts/Form.vue'
import Item from '../pages/carts/Item.vue'
import CartDetails from '../pages/carts/components/CartDetails.vue'
import CheckOut from '../pages/carts/components/CheckOut.vue'

routes_list = {

    path: '/carts',
    name: 'carts.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'carts.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'carts.view',
            component: Item,
            props: true,
        }
    ]
};

let cart_details ={
        path: 'cart-details/:id?',
        name: 'carts.details',
        component: CartDetails,
        props: true,
    };

let check_out ={
    path: 'cart-check-out/:id?',
    name: 'carts.check_out',
    component: CheckOut,
    props: true,
};
routes.push(routes_list);
routes.push(cart_details);
routes.push(check_out);

export default routes;


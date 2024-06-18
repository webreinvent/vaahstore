let routes= [];
let routes_list= [];

import List from '../pages/carts/List.vue'

import CartDetails from '../pages/carts/components/CartDetails.vue'
import CheckOut from '../pages/carts/components/CheckOut.vue'
import OrderDetails from '../pages/carts/components/OrderDetails.vue'

routes_list = {

    path: '/carts',
    name: 'carts.index',
    component: List,
    props: true,
    children:[

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
let order_detail ={
    path: 'cart-order-details/:order_id?',
    name: 'carts.order_details',
    component: OrderDetails,
    props: true,
};
routes.push(routes_list);
routes.push(cart_details);
routes.push(check_out);
routes.push(order_detail);

export default routes;


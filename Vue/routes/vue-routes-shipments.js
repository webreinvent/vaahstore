
let routes= [];
let routes_list= [];

import List from '../pages/shipments/List.vue'
import Form from '../pages/shipments/Form.vue'
import Item from '../pages/shipments/Item.vue'

routes_list = {

    path: '/shipments',
    name: 'shipments.index',
    component: List,
    props: true,
    children:[

        {
            path: 'view/:id?',
            name: 'shipments.view',
            component: Item,
            props: true,
        }
    ]
};
let shipment_form ={
    path: 'form/:id?',
    name: 'shipments.form',
    component: Form,
    props: true,
};
routes.push(shipment_form);
routes.push(routes_list);

export default routes;


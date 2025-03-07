let routes= [];
let routes_list= [];

import List from '../pages/storepaymentmethods/List.vue'
import Form from '../pages/storepaymentmethods/Form.vue'
import Item from '../pages/storepaymentmethods/Item.vue'
import Filters from '../pages/storepaymentmethods/Filters.vue'

routes_list = {

    path: '/storepaymentmethods',
    name: 'storepaymentmethods.index',
    component: List,
    props: true,
    children:[
        {
            path: 'form/:id?',
            name: 'storepaymentmethods.form',
            component: Form,
            props: true,
        },
        {
            path: 'view/:id?',
            name: 'storepaymentmethods.view',
            component: Item,
            props: true,
        },
        {
            path: 'filters',
            name: 'storepaymentmethods.filters',
            component: Filters,
            props: true,
        },
    ]
};

routes.push(routes_list);

export default routes;


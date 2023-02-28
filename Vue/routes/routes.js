let routes= [];

import dashboard from "./vue-routes-dashboard";
import store from "./vue-routes-store";
import product from "./vue-routes-products";

routes = routes.concat(dashboard);
routes = routes.concat(store);
routes = routes.concat(product);

export default routes;

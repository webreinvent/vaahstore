let routes= [];

import dashboard from "./vue-routes-dashboard";
import product from "./vue-routes-products";
import vendor from "./vue-routes-vendors";

routes = routes.concat(dashboard);
routes = routes.concat(product);
routes = routes.concat(vendor);

export default routes;

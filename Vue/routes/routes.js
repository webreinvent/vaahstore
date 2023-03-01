let routes= [];

import dashboard from "./vue-routes-dashboard";
import brand from "./vue-routes-brands";
import store from "./vue-routes-store";
import product from "./vue-routes-products";
import vendor from "./vue-routes-vendors";

routes = routes.concat(dashboard);
routes = routes.concat(brand);
routes = routes.concat(store);
routes = routes.concat(product);
routes = routes.concat(vendor);

export default routes;

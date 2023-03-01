let routes= [];

import dashboard from "./vue-routes-dashboard";
import product from "./vue-routes-products";

routes = routes.concat(dashboard);
routes = routes.concat(product);

export default routes;

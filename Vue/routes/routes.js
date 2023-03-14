let routes= [];

import dashboard from "./vue-routes-dashboard";
import brand from "./vue-routes-brands";
import store from "./vue-routes-store";
import vendor from "./vue-routes-vendors";
import product from "./vue-routes-products";
import productvendors from "./vue-routes-productvendors";

routes = routes.concat(dashboard);
routes = routes.concat(brand);
routes = routes.concat(store);
routes = routes.concat(vendor);
routes = routes.concat(product);
routes = routes.concat(productvendors);

export default routes;

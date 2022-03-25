let routes=[];

import dashboard from "./routes-dashboard";
import stores from "./vue-routes-stores";
import vendors from "./vue-routes-vendors";
import brands from "./vue-routes-brands";
import products from "./vue-routes-products";

routes = routes.concat(dashboard);
routes = routes.concat(stores);
routes = routes.concat(vendors);
routes = routes.concat(brands);
routes = routes.concat(products);

export default routes;

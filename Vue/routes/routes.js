let routes=[];

import dashboard from "./routes-dashboard";
import stores from "./vue-routes-stores";
import vendors from "./vue-routes-vendors";

routes = routes.concat(dashboard);
routes = routes.concat(stores);
routes = routes.concat(vendors);

export default routes;

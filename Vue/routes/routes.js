let routes= [];

// import dashboard from "./vue-routes-dashboard";
import brand from "./vue-routes-brands";
import store from "./vue-routes-store";
import vendor from "./vue-routes-vendors";
import product from "./vue-routes-products";
import warehouse from "./vue-routes-warehouses";
import productvendors from "./vue-routes-productvendors";
import productvariation from "./vue-routes-productvariations";
import productmedias from "./vue-routes-productmedias";
import productprices from "./vue-routes-productprices";
import productstocks from "./vue-routes-productstocks";
import attributes from "./vue-routes-attributes";
import orders from "./vue-routes-orders";
import paymentmethods from "./vue-routes-paymentmethods";

// routes = routes.concat(dashboard);
routes = routes.concat(brand);
routes = routes.concat(store);
routes = routes.concat(vendor);
routes = routes.concat(product);
routes = routes.concat(warehouse);
routes = routes.concat(productvendors);
routes = routes.concat(productvariation);
routes = routes.concat(productmedias);
routes = routes.concat(productprices);
routes = routes.concat(orders);
routes = routes.concat(productstocks);
routes = routes.concat(attributes);
routes = routes.concat(paymentmethods);

export default routes;

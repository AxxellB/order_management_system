import { RouterProvider, createBrowserRouter } from "react-router-dom";
import { useAuth } from "../provider/AuthProvider";
import { ProtectedRoute } from "./ProtectedRoute";
import Login from "../pages/Login";
import Logout from "../pages/Logout";
import Homepage from "../pages/Homepage";
import Register from "../pages/Register";
import Basket from "../pages/Basket";
import Layout from "../components/Layout";
import Checkout from "../pages/Checkout";
import ProductsList from "../pages/ProductList";
import ProductNew from "../pages/ProductNew";
import ProductDetails from "../pages/ProductDetails";
import ProductEdit from "../pages/ProductEdit";
import React from "react";
import OrderManagementPage from "../pages/OrderManagementPage";
import EditOrderForm from "../components/EditOrderForm";
import UserProfile from "../components/UserProfile";
import SecurityCentre from "../components/SecurityCentre";
import AddressTab from "../components/AddressTab";
import MyOrdersTab from "../components/MyOrdersTab";
import MyOrdersDetailsTab from "../components/MyOrdersDetailsTab";
import CategoriesList from "../pages/CategoryList";
import CategoryNew from "../pages/CategoryNew";
import CategoryEdit from "../pages/CategoryEdit";
import SendEmail from "../pages/SendEmail";
import OrderAuditLogs from "../pages/OrderAuditLogs";
import ViewOrderLog from "../components/ViewOrderHistoryLog";

const Routes = () => {
    const { token } = useAuth();

    const routesForPublic = [
        {
            path: "/",
            element: <Layout />,
            children: [
                {
                    path: "/",
                    element: <Homepage />,
                },
                {
                    path:  "react/send-email",
                    element: <SendEmail />
                },
            ],
        },
    ];

    const routesForAuthenticatedOnly = [
        {
            path: "/",
            element: <ProtectedRoute />,
            children: [
                {
                    path: "/",
                    element: <Layout />,
                    children: [
                        {
                            path: "/profile",
                            element: <UserProfile />
                        },
                        {
                            path: "/profile/security-centre",
                            element: <SecurityCentre />
                        },
                        {
                            path: "/profile/addresses",
                            element: <AddressTab />
                        },
                        {
                            path: "/profile/orders",
                            element: <MyOrdersTab />
                        },
                        {
                            path: `/profile/orders/:id`,
                            element: <MyOrdersDetailsTab />
                        },
                        {
                            path: "/admin/products",
                            element: <ProductsList />,
                        },
                        {
                            path: "/admin/products/new",
                            element: <ProductNew />,
                        },
                        {
                            path: "/admin/products/:id",
                            element: <ProductDetails />,
                        },
                        {
                            path: "/admin/products/edit/:id",
                            element: <ProductEdit />,
                        },
                        {
                            path: "/admin/categories",
                            element: <CategoriesList />,
                        },
                        {
                            path: "/admin/categories/new",
                            element: <CategoryNew />,
                        },
                        {
                            path: "/admin/categories/:id",
                            element: <CategoryNew />,
                        },
                        {
                            path: "/admin/categories/edit/:id",
                            element: <CategoryEdit />,
                        },
                        {
                            path: "/admin/orders",
                            element: <OrderManagementPage />
                        },
                        {
                            path: "/admin/order/:id",
                            element: <EditOrderForm />
                        },
                        {
                            path: "/admin/order-history-logs",
                            element: <OrderAuditLogs />,
                        },
                        {
                            path: "/admin/order-history-logs/:id",
                            element: <ViewOrderLog />
                        },
                        {
                            path: "/profile/security-centre",
                            element: <SecurityCentre />
                        },
                        {
                            path: "/profile/addresses",
                            element: <AddressTab />
                        },
                        {
                            path: "/profile/orders",
                            element: <MyOrdersTab />
                        },
                        {
                            path: `/profile/orders/:id`,
                            element: <MyOrdersDetailsTab />
                        },
                        {
                            path: "/basket",
                            element: <Basket />
                        },
                        {
                            path: "/checkout",
                            element: <Checkout />
                        },
                        {
                            path: "/logout",
                            element: <Logout />
                        },
                    ],
                },
            ],
        },
    ];

    const routesForNotAuthenticatedOnly = [
        {
            path: "/",
            element: <Layout />,
            children: [
                {
                    path: "/login",
                    element: <Login />,
                },
                {
                    path: "/register",
                    element: <Register />,
                },
            ]
        }
    ];

    const router = createBrowserRouter([
        ...routesForPublic,
        ...(!token ? routesForNotAuthenticatedOnly : []),
        ...routesForAuthenticatedOnly,
    ]);

    return <RouterProvider router={router}/>
}

export default Routes;

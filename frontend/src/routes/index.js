import { RouterProvider, createBrowserRouter } from "react-router-dom";
import { useAuth } from "../provider/AuthProvider";
import { ProtectedRoute } from "./ProtectedRoute";
import LoginPage from "../pages/LoginPage";
import Logout from "../pages/Logout";
import Homepage from "../pages/Homepage";
import RegisterPage from "../pages/RegisterPage";
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
                    path: "/admin/orders",
                    element: <OrderManagementPage />
                },
                {
                    path: "/admin/order/:id",
                    element: <EditOrderForm />
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
                            element: <div>User Profile</div>,
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
                    element: <LoginPage />,
                },
                {
                    path: "/register",
                    element: <RegisterPage />,
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

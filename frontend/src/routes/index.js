import { RouterProvider, createBrowserRouter } from "react-router-dom";
import { useAuth } from "../provider/AuthProvider";
import { ProtectedRoute } from "./ProtectedRoute";
import LoginPage from "../pages/LoginPage";
import Logout from "../pages/Logout";
import Homepage from "../pages/Homepage";
import RegisterPage from "../pages/RegisterPage";
import Basket from "../pages/Basket";
import Layout from "../components/Layout";

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

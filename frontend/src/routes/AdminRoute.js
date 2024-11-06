import { Navigate, Outlet } from "react-router-dom";
import { useAuth } from "../provider/AuthProvider";

export const AdminRoute = () => {
    const { isAdmin } = useAuth();

    if (!isAdmin) {
        return <Navigate to="/" />;
    }

    return <Outlet />;
};
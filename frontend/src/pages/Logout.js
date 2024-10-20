import { useNavigate } from "react-router-dom";
import { useAuth } from "../provider/AuthProvider";

const Logout = () => {
    const { setToken } = useAuth();
    const navigate = useNavigate();

    const handleLogout = () => {
        setToken();
        navigate("/", { replace: true });
    };

    setTimeout(() => {
        handleLogout();
    }, 1000);

    return <>You are being logged out...</>;
};

export default Logout;
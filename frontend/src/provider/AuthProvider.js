import axios from "axios";
import { createContext, useContext, useEffect, useMemo, useState } from "react";
import {jwtDecode} from "jwt-decode";

const AuthContext = createContext();

const AuthProvider = ({ children }) => {
    const [token, setToken_] = useState(localStorage.getItem("token"));
    const [user, setUser] = useState(null);
    const [isAdmin, setIsAdmin] = useState(false)

    const setToken = (newToken) => {
        setToken_(newToken);
    };

    useEffect(() => {
        if (token) {
            axios.defaults.headers.common["Authorization"] = "Bearer " + token;
            localStorage.setItem('token',token);

            const decodedToken = jwtDecode(token)
            setUser(decodedToken.username);
            if(decodedToken.roles && decodedToken.roles.includes('ROLE_ADMIN')) {
                setIsAdmin(true);
            }
        } else {
            delete axios.defaults.headers.common["Authorization"];
            localStorage.removeItem('token')
            setUser(null)
            setIsAdmin(false)
        }
    }, [token]);

    const contextValue = useMemo(
        () => ({
            token,
            setToken,
            user,
            isAdmin,
        }),
        [token, user, isAdmin]
    );

    return (
        <AuthContext.Provider value={contextValue}>{children}</AuthContext.Provider>
    );
};

export const useAuth = () => {
    return useContext(AuthContext);
};

export default AuthProvider;
// components/PublicRoute.jsx
import { Navigate, Outlet } from "react-router-dom";
import { useAuth } from "./AuthContext";

const PublicRoute = () => {
//   const isAuthenticated = !!localStorage.getItem("authData");

  const { isAuthenticated, user } = useAuth();

  if(!isAuthenticated){
    return <Outlet />
  }

  if(user?.role_id===1){
    return <Navigate to="/admin/dashboard" replace />
  }

  return <Navigate to="/home" replace />
};

export default PublicRoute;

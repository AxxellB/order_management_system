import RegisterPage from "./pages/RegisterPage";
import LoginPage from "./pages/LoginPage";
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Homepage from "./pages/Homepage";
import OrderManagementPage from "./pages/OrderManagementPage";
import React from 'react';
import './App.css';
import 'bootstrap/dist/css/bootstrap.min.css';
import Navigation from "./components/Navigation";
import {useState} from "react";
import EditOrderForm from "./components/EditOrderForm";

function App() {
    const [user, setUser] = useState(null);
    const isAdmin = false;

  return (
      <Router>
          <Navigation user={user} isAdmin={isAdmin} />
          <Routes>
              <Route path="/" element={<Homepage />} />
              <Route path="/login" element={<LoginPage setUser={setUser} />} />
              <Route path="/register" element={<RegisterPage />} />
              <Route path="/admin/orders" element={<OrderManagementPage />} />
              <Route path="/admin/order/:id" element={<EditOrderForm />} />
          </Routes>
      </Router>
  );
}

export default App;

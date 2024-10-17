import RegisterPage from "./pages/RegisterPage";
import LoginPage from "./pages/LoginPage";
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Homepage from "./pages/Homepage";
import React from 'react';
import './App.css'; // Import global styles (optional)
import 'bootstrap/dist/css/bootstrap.min.css';
import Navigation from "./components/Navigation";
import {useState} from "react";

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
          </Routes>
      </Router>
  );
}

export default App;

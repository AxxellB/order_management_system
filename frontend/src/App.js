import RegisterPage from "./pages/RegisterPage";
import LoginPage from "./pages/LoginPage";
import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import Homepage from "./pages/Homepage";
import React from 'react';
import './App.css'; // Import global styles (optional)
import 'bootstrap/dist/css/bootstrap.min.css';

function App() {
  return (
      <Router>
        <Routes>
            <Route path="/homepage" element={<Homepage />}></Route>
            <Route path="/login" element={<LoginPage />} />
            <Route path="/register" element={<RegisterPage />} />
        </Routes>
      </Router>
  );
}

export default App;

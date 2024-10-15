import logo from './logo.svg';
import './App.css';
import {useEffect} from "react";

function App() {
  useEffect(() => {
    fetch('http://0.0.0.0/api/register', {method: 'POST'})
        .then(response => {
          if (!response.ok) {
            throw new Error('Network response was not ok');
          }
          return response.json();
        })
        .then(data => console.log(data))
        .catch(error => console.error('There was a problem with the fetch operation:', error));
  }, []);
  return (
    <div className="App">
      <header className="App-header">
        <img src={logo} className="App-logo" alt="logo" />
        <p>
          Edit <code>src/App.js</code> and save to reload.
        </p>
        <a
          className="App-link"
          href="https://reactjs.org"
          target="_blank"
          rel="noopener noreferrer"
        >
          Learn React
        </a>
      </header>
    </div>
  );
}

export default App;

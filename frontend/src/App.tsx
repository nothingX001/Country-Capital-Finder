import { BrowserRouter as Router, Routes, Route } from 'react-router-dom';
import NavBar from './components/NavBar';
import Home from './pages/Home';
import CountryProfiles from './pages/CountryProfiles';
import CountryDetail from './pages/CountryDetail';
import Quiz from './pages/Quiz';
import WorldMap from './pages/WorldMap';
import About from './pages/About';
import './styles/App.css';

function App() {
  return (
    <Router>
      <div className="app">
        <NavBar />
        <main className="page-content">
          <Routes>
            <Route path="/" element={<Home />} />
            <Route path="/profiles" element={<CountryProfiles />} />
            <Route path="/country/:id" element={<CountryDetail />} />
            <Route path="/quiz" element={<Quiz />} />
            <Route path="/map" element={<WorldMap />} />
            <Route path="/about" element={<About />} />
          </Routes>
        </main>
      </div>
    </Router>
  );
}

export default App;

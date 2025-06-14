import { useState } from 'react';
import { Link, useLocation } from 'react-router-dom';
import '../styles/NavBar.css';

const NavBar = () => {
  const [isMenuOpen, setIsMenuOpen] = useState(false);
  const location = useLocation();

  const toggleMenu = () => {
    setIsMenuOpen(!isMenuOpen);
  };

  const isActive = (path: string) => {
    return location.pathname === path;
  };

  return (
    <nav className={`navbar ${isMenuOpen ? 'menu-active' : ''}`}>
      <div className="navbar-container">
        <Link to="/" className="navbar-logo">
          <img src="/logo.png" alt="ExploreCapitals" />
        </Link>
        <button className="navbar-toggle" onClick={toggleMenu}>
          <span></span>
          <span></span>
          <span></span>
        </button>
        <ul className={`navbar-list ${isMenuOpen ? 'open' : ''}`}>
          <li>
            <Link to="/" className={isActive('/') ? 'active' : ''}>
              Home
            </Link>
          </li>
          <li>
            <Link to="/profiles" className={isActive('/profiles') ? 'active' : ''}>
              Country Profiles
            </Link>
          </li>
          <li>
            <Link to="/quiz" className={isActive('/quiz') ? 'active' : ''}>
              Quiz
            </Link>
          </li>
          <li>
            <Link to="/map" className={isActive('/map') ? 'active' : ''}>
              World Map
            </Link>
          </li>
          <li>
            <Link to="/about" className={isActive('/about') ? 'active' : ''}>
              About
            </Link>
          </li>
        </ul>
      </div>
    </nav>
  );
};

export default NavBar; 
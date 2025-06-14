import { useState, useEffect, useRef } from 'react';
import { useNavigate } from 'react-router-dom';
import '../styles/Home.css';

interface Country {
  country_name: string;
  capital_name: string;
}

const Home = () => {
  const [query, setQuery] = useState('');
  const [results, setResults] = useState<string[]>([]);
  const [selectedCountry, setSelectedCountry] = useState<Country | null>(null);
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(false);
  const searchRef = useRef<HTMLDivElement>(null);
  const navigate = useNavigate();

  useEffect(() => {
    const handleClickOutside = (event: MouseEvent) => {
      if (searchRef.current && !searchRef.current.contains(event.target as Node)) {
        setResults([]);
      }
    };

    document.addEventListener('mousedown', handleClickOutside);
    return () => document.removeEventListener('mousedown', handleClickOutside);
  }, []);

  const handleSearch = async (searchQuery: string) => {
    setQuery(searchQuery);
    if (searchQuery.length < 2) {
      setResults([]);
      return;
    }

    try {
      const response = await fetch(`/api/countries?type=member&query=${encodeURIComponent(searchQuery)}`);
      const data = await response.json();
      if (data.error) {
        setError(data.error);
        setResults([]);
      } else {
        setResults(data.map((country: any) => country.country_name));
        setError('');
      }
    } catch (err) {
      setError('Failed to fetch countries');
      setResults([]);
    }
  };

  const handleSelect = async (country: string) => {
    setQuery(country);
    setResults([]);
    setIsLoading(true);
    setError('');

    try {
      const response = await fetch(`/api/capital?country=${encodeURIComponent(country)}`);
      const data = await response.json();
      if (data.error) {
        setError(data.error);
        setSelectedCountry(null);
      } else {
        setSelectedCountry(data);
      }
    } catch (err) {
      setError('Failed to fetch capital');
      setSelectedCountry(null);
    } finally {
      setIsLoading(false);
    }
  };

  return (
    <div className="home">
      <h1>Find Capital Cities</h1>
      <div className="search-container" ref={searchRef}>
        <div className="search-bar-container">
          <input
            type="text"
            value={query}
            onChange={(e) => handleSearch(e.target.value)}
            placeholder="Enter a country name..."
            aria-label="Search country"
          />
        </div>
        {results.length > 0 && (
          <ul className="autocomplete-dropdown">
            {results.map((country) => (
              <li key={country} onClick={() => handleSelect(country)}>
                {country}
              </li>
            ))}
          </ul>
        )}
      </div>

      {isLoading && (
        <div className="loading-indicator">
          <div className="spinner"></div>
        </div>
      )}

      {error && <div className="message error">{error}</div>}

      {selectedCountry && (
        <div id="countryProfileCard">
          <h2>{selectedCountry.country_name}</h2>
          <div className="attributes">
            <p>
              <strong>Capital:</strong> {selectedCountry.capital_name}
            </p>
          </div>
        </div>
      )}
    </div>
  );
};

export default Home; 
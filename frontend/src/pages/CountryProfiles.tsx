import { useState, useEffect } from 'react';
import { Link } from 'react-router-dom';
import '../styles/CountryProfiles.css';

interface Country {
  id: number;
  country_name: string;
  flag_emoji: string;
  iso_code: string;
}

const CountryProfiles = () => {
  const [countries, setCountries] = useState<Country[]>([]);
  const [type, setType] = useState<'member' | 'territory' | 'defacto'>('member');
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchCountries = async () => {
      setIsLoading(true);
      setError('');

      try {
        const response = await fetch(`/api/countries?type=${type}`);
        const data = await response.json();
        if (data.error) {
          setError(data.error);
          setCountries([]);
        } else {
          setCountries(data);
        }
      } catch (err) {
        setError('Failed to fetch countries');
        setCountries([]);
      } finally {
        setIsLoading(false);
      }
    };

    fetchCountries();
  }, [type]);

  return (
    <div className="country-profiles">
      <h1>Country Profiles</h1>
      <div className="type-selector">
        <button
          className={type === 'member' ? 'active' : ''}
          onClick={() => setType('member')}
        >
          UN Members
        </button>
        <button
          className={type === 'territory' ? 'active' : ''}
          onClick={() => setType('territory')}
        >
          Territories
        </button>
        <button
          className={type === 'defacto' ? 'active' : ''}
          onClick={() => setType('defacto')}
        >
          De Facto States
        </button>
      </div>

      {isLoading && (
        <div className="loading-indicator">
          <div className="spinner"></div>
        </div>
      )}

      {error && <div className="message error">{error}</div>}

      {!isLoading && !error && (
        <div className="country-list">
          {countries.map((country) => (
            <Link
              key={country.id}
              to={`/country/${country.id}`}
              className="country-item"
            >
              <span className="flag-emoji">{country.flag_emoji}</span>
              <span className="country-name">{country.country_name}</span>
            </Link>
          ))}
        </div>
      )}
    </div>
  );
};

export default CountryProfiles; 
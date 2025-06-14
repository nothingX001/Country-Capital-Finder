import { useState, useEffect } from 'react';
import { useParams, Link } from 'react-router-dom';
import '../styles/CountryDetail.css';

interface Country {
  country_name: string;
  flag_emoji: string;
  iso_code: string;
  language: string;
  flag_image_url: string;
  status: string;
  disclaimer: string;
  parent_id: number | null;
  capitals: string[];
}

const CountryDetail = () => {
  const { id } = useParams<{ id: string }>();
  const [country, setCountry] = useState<Country | null>(null);
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchCountry = async () => {
      setIsLoading(true);
      setError('');

      try {
        const response = await fetch(`/api/country/${id}`);
        const data = await response.json();
        if (data.error) {
          setError(data.error);
          setCountry(null);
        } else {
          setCountry(data);
        }
      } catch (err) {
        setError('Failed to fetch country details');
        setCountry(null);
      } finally {
        setIsLoading(false);
      }
    };

    if (id) {
      fetchCountry();
    }
  }, [id]);

  if (isLoading) {
    return (
      <div className="loading-indicator">
        <div className="spinner"></div>
      </div>
    );
  }

  if (error) {
    return <div className="message error">{error}</div>;
  }

  if (!country) {
    return <div className="message">Country not found</div>;
  }

  return (
    <div className="country-detail">
      <div className="country-detail-header">
        <h1>
          <span className="flag-emoji">{country.flag_emoji}</span>
          {country.country_name}
        </h1>
        {country.flag_image_url && (
          <div className="flag-image">
            <img src={country.flag_image_url} alt={`${country.country_name} flag`} />
          </div>
        )}
      </div>

      <div className="country-detail-content">
        <div className="attributes">
          <p>
            <strong>Status:</strong> {country.status}
          </p>
          <p>
            <strong>ISO Code:</strong> {country.iso_code}
          </p>
          <p>
            <strong>Language:</strong> {country.language}
          </p>
          <p>
            <strong>Capitals:</strong>{' '}
            {country.capitals.length > 0
              ? country.capitals.join(', ')
              : 'No capital city'}
          </p>
          {country.parent_id && (
            <p>
              <strong>Part of:</strong>{' '}
              <Link to={`/country/${country.parent_id}`}>View Parent Country</Link>
            </p>
          )}
        </div>

        {country.disclaimer && (
          <div className="disclaimer">
            <p>{country.disclaimer}</p>
          </div>
        )}
      </div>
    </div>
  );
};

export default CountryDetail; 
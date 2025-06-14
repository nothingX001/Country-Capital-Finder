import { useState, useEffect } from 'react';
import '../styles/About.css';

interface Statistics {
  member_count: number;
  territory_count: number;
  defacto_count: number;
  observer_count: number;
  language_count: number;
}

const About = () => {
  const [stats, setStats] = useState<Statistics | null>(null);
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchStats = async () => {
      setIsLoading(true);
      setError('');

      try {
        const response = await fetch('/api/statistics');
        const data = await response.json();
        if (data.error) {
          setError(data.error);
          setStats(null);
        } else {
          setStats(data);
        }
      } catch (err) {
        setError('Failed to fetch statistics');
        setStats(null);
      } finally {
        setIsLoading(false);
      }
    };

    fetchStats();
  }, []);

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

  return (
    <div className="about">
      <h1>About ExploreCapitals</h1>
      <p>
        ExploreCapitals is a comprehensive resource for learning about countries and their capital
        cities. Our database includes information about UN member states, territories, and de facto
        states, along with their official languages and other relevant details.
      </p>

      {stats && (
        <div className="statistics">
          <h2>Database Statistics</h2>
          <ul>
            <li>
              <strong>UN Member States:</strong> {stats.member_count}
            </li>
            <li>
              <strong>Territories:</strong> {stats.territory_count}
            </li>
            <li>
              <strong>De Facto States:</strong> {stats.defacto_count}
            </li>
            <li>
              <strong>UN Observer States:</strong> {stats.observer_count}
            </li>
            <li>
              <strong>Unique Languages:</strong> {stats.language_count}
            </li>
          </ul>
        </div>
      )}

      <div className="features">
        <h2>Features</h2>
        <ul>
          <li>
            <strong>Country Search:</strong> Find any country's capital city instantly
          </li>
          <li>
            <strong>Country Profiles:</strong> Detailed information about each country
          </li>
          <li>
            <strong>Interactive Quiz:</strong> Test your knowledge of capital cities
          </li>
          <li>
            <strong>World Map:</strong> Visualize countries and their capitals
          </li>
        </ul>
      </div>

      <div className="disclaimer">
        <h2>Disclaimer</h2>
        <p>
          The information provided on this website is for educational purposes only. While we strive
          to maintain accurate and up-to-date information, geopolitical situations and country
          statuses may change. Please refer to official sources for the most current information.
        </p>
      </div>
    </div>
  );
};

export default About; 
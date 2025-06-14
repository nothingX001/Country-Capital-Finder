import { useState, useEffect, useRef } from 'react';
import mapboxgl from 'mapbox-gl';
import 'mapbox-gl/dist/mapbox-gl.css';
import '../styles/WorldMap.css';

interface MapPoint {
  id: number;
  country_name: string;
  capital_name: string | null;
  latitude: string;
  longitude: string;
  iso_code: string;
  flag_emoji: string;
  sort_order: number;
}

const WorldMap = () => {
  const mapContainer = useRef<HTMLDivElement>(null);
  const map = useRef<mapboxgl.Map | null>(null);
  const [points, setPoints] = useState<MapPoint[]>([]);
  const [error, setError] = useState('');
  const [isLoading, setIsLoading] = useState(true);

  useEffect(() => {
    const fetchMapData = async () => {
      setIsLoading(true);
      setError('');

      try {
        const response = await fetch('/api/map');
        const data = await response.json();
        if (data.error) {
          setError(data.error);
          setPoints([]);
        } else {
          setPoints(data);
        }
      } catch (err) {
        setError('Failed to fetch map data');
        setPoints([]);
      } finally {
        setIsLoading(false);
      }
    };

    fetchMapData();
  }, []);

  useEffect(() => {
    if (!mapContainer.current || !points.length) return;

    mapboxgl.accessToken = process.env.VITE_MAPBOX_TOKEN || '';

    map.current = new mapboxgl.Map({
      container: mapContainer.current,
      style: 'mapbox://styles/mapbox/light-v11',
      center: [0, 20],
      zoom: 1.5,
    });

    map.current.on('load', () => {
      // Add country markers
      map.current?.addSource('countries', {
        type: 'geojson',
        data: {
          type: 'FeatureCollection',
          features: points
            .filter((point) => point.sort_order === 1)
            .map((point) => ({
              type: 'Feature',
              properties: {
                id: point.id,
                name: point.country_name,
                flag: point.flag_emoji,
                iso: point.iso_code,
              },
              geometry: {
                type: 'Point',
                coordinates: [parseFloat(point.longitude), parseFloat(point.latitude)],
              },
            })),
        },
      });

      // Add capital markers
      map.current?.addSource('capitals', {
        type: 'geojson',
        data: {
          type: 'FeatureCollection',
          features: points
            .filter((point) => point.sort_order === 0)
            .map((point) => ({
              type: 'Feature',
              properties: {
                id: point.id,
                name: point.capital_name,
                country: point.country_name,
                flag: point.flag_emoji,
                iso: point.iso_code,
              },
              geometry: {
                type: 'Point',
                coordinates: [parseFloat(point.longitude), parseFloat(point.latitude)],
              },
            })),
        },
      });

      // Add country markers layer
      map.current?.addLayer({
        id: 'country-markers',
        type: 'circle',
        source: 'countries',
        paint: {
          'circle-radius': 6,
          'circle-color': '#2A363B',
          'circle-stroke-width': 2,
          'circle-stroke-color': '#DCCB9C',
        },
      });

      // Add capital markers layer
      map.current?.addLayer({
        id: 'capital-markers',
        type: 'circle',
        source: 'capitals',
        paint: {
          'circle-radius': 4,
          'circle-color': '#DCCB9C',
          'circle-stroke-width': 2,
          'circle-stroke-color': '#2A363B',
        },
      });

      // Add popups
      map.current?.on('click', 'country-markers', (e) => {
        if (!e.features?.[0]) return;

        const { name, flag } = e.features[0].properties;
        new mapboxgl.Popup()
          .setLngLat(e.lngLat)
          .setHTML(`<div class="map-popup"><span class="flag-emoji">${flag}</span> ${name}</div>`)
          .addTo(map.current!);
      });

      map.current?.on('click', 'capital-markers', (e) => {
        if (!e.features?.[0]) return;

        const { name, country, flag } = e.features[0].properties;
        new mapboxgl.Popup()
          .setLngLat(e.lngLat)
          .setHTML(
            `<div class="map-popup"><span class="flag-emoji">${flag}</span> ${name}, ${country}</div>`
          )
          .addTo(map.current!);
      });

      // Change cursor on hover
      map.current?.on('mouseenter', 'country-markers', () => {
        map.current!.getCanvas().style.cursor = 'pointer';
      });

      map.current?.on('mouseleave', 'country-markers', () => {
        map.current!.getCanvas().style.cursor = '';
      });

      map.current?.on('mouseenter', 'capital-markers', () => {
        map.current!.getCanvas().style.cursor = 'pointer';
      });

      map.current?.on('mouseleave', 'capital-markers', () => {
        map.current!.getCanvas().style.cursor = '';
      });
    });

    return () => {
      map.current?.remove();
    };
  }, [points]);

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
    <div className="world-map">
      <h1>World Map</h1>
      <div className="map-container">
        <div ref={mapContainer} className="map" />
      </div>
    </div>
  );
};

export default WorldMap; 
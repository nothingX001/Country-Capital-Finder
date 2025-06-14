import { APIGatewayProxyEvent, APIGatewayProxyResult } from 'aws-lambda';
import { query, validateCountryType, validateQuizType, errorResponse, successResponse } from './utils';

// GET /api/capital?country=France
export async function getCapital(event: APIGatewayProxyEvent): Promise<APIGatewayProxyResult> {
  const country = event.queryStringParameters?.country;
  if (!country) {
    return errorResponse('Country parameter is required');
  }

  try {
    const rows = await query(`
      SELECT c."Country Name" as country_name, cap.capital_name
      FROM countries c
      JOIN capitals cap ON c.id = cap.country_id
      WHERE LOWER(c."Country Name") = LOWER($1)
      LIMIT 1
    `, [country]);

    if (rows.length === 0) {
      return errorResponse('Country not found');
    }

    return successResponse(rows[0]);
  } catch (error) {
    console.error('Error fetching capital:', error);
    return errorResponse('Failed to fetch capital');
  }
}

// GET /api/countries?type=member|territory|defacto
export async function getCountries(event: APIGatewayProxyEvent): Promise<APIGatewayProxyResult> {
  const type = event.queryStringParameters?.type;
  if (!type || !validateCountryType(type)) {
    return errorResponse('Invalid country type');
  }

  try {
    const statusMap = {
      member: 'UN member',
      territory: 'Territory',
      defacto: 'De facto state'
    };

    const rows = await query(`
      SELECT id, "Country Name" as country_name, "Flag Emoji" as flag_emoji, "ISO Alpha-2" as iso_code
      FROM countries
      WHERE status = $1
      ORDER BY "Country Name" ASC
    `, [statusMap[type as keyof typeof statusMap]]);

    return successResponse(rows);
  } catch (error) {
    console.error('Error fetching countries:', error);
    return errorResponse('Failed to fetch countries');
  }
}

// GET /api/country/:id
export async function getCountryDetail(event: APIGatewayProxyEvent): Promise<APIGatewayProxyResult> {
  const id = event.pathParameters?.id;
  if (!id) {
    return errorResponse('Country ID is required');
  }

  try {
    const rows = await query(`
      SELECT 
        c."Country Name" as country_name,
        c."Flag Emoji" as flag_emoji,
        c."ISO Alpha-2" as iso_code,
        c.language,
        c.flag_url as flag_image_url,
        c.status,
        c.disclaimer,
        c.parent_id,
        array_agg(cap.capital_name) as capitals
      FROM countries c
      LEFT JOIN capitals cap ON c.id = cap.country_id
      WHERE c.id = $1
      GROUP BY c.id, c."Country Name", c."Flag Emoji", c."ISO Alpha-2", 
               c.language, c.flag_url, c.status, c.disclaimer, c.parent_id
    `, [id]);

    if (rows.length === 0) {
      return errorResponse('Country not found');
    }

    // Process capitals array
    const country = rows[0];
    if (country.capitals && country.capitals[0] !== null) {
      country.capitals = country.capitals.map((cap: string) => cap.replace(/[{}"]/g, ''));
    } else {
      country.capitals = [];
    }

    return successResponse(country);
  } catch (error) {
    console.error('Error fetching country detail:', error);
    return errorResponse('Failed to fetch country detail');
  }
}

// GET /api/quiz?type=member|territory&limit=10
export async function getQuiz(event: APIGatewayProxyEvent): Promise<APIGatewayProxyResult> {
  const type = event.queryStringParameters?.type;
  const limit = parseInt(event.queryStringParameters?.limit || '10');

  if (!type || !validateQuizType(type)) {
    return errorResponse('Invalid quiz type');
  }

  if (isNaN(limit) || limit < 1 || limit > 50) {
    return errorResponse('Invalid limit parameter');
  }

  try {
    const statusMap = {
      member: 'UN member',
      territory: 'Territory'
    };

    const rows = await query(`
      WITH random_countries AS (
        SELECT 
          c.id,
          c."Country Name" as country_name,
          c."Flag Emoji" as flag_emoji,
          c."ISO Alpha-2" as iso_code,
          array_agg(cap.capital_name) as capitals
        FROM countries c
        JOIN capitals cap ON c.id = cap.country_id
        WHERE c.status = $1
        GROUP BY c.id, c."Country Name", c."Flag Emoji", c."ISO Alpha-2"
        ORDER BY RANDOM()
        LIMIT $2
      )
      SELECT * FROM random_countries
      WHERE array_length(capitals, 1) > 0
    `, [statusMap[type as keyof typeof statusMap], limit]);

    // Process capitals arrays
    const countries = rows.map(row => ({
      ...row,
      capitals: row.capitals.map((cap: string) => cap.replace(/[{}"]/g, ''))
    }));

    return successResponse(countries);
  } catch (error) {
    console.error('Error generating quiz:', error);
    return errorResponse('Failed to generate quiz');
  }
}

// GET /api/map
export async function getMapData(event: APIGatewayProxyEvent): Promise<APIGatewayProxyResult> {
  try {
    const rows = await query(`
      (
        SELECT
          id,
          "Country Name" as country_name,
          NULL::text as capital_name,
          "Coordinates (Latitude)"::text as latitude,
          "Coordinates (Longitude)"::text as longitude,
          "ISO Alpha-2" as iso_code,
          "Flag Emoji" as flag_emoji,
          1 as sort_order
        FROM countries
        WHERE "Coordinates (Latitude)" IS NOT NULL
          AND "Coordinates (Longitude)" IS NOT NULL
      )
      UNION ALL
      (
        SELECT
          cap.id,
          c."Country Name" as country_name,
          cap.capital_name,
          cap.latitude::text as latitude,
          cap.longitude::text as longitude,
          c."ISO Alpha-2" as iso_code,
          c."Flag Emoji" as flag_emoji,
          0 as sort_order
        FROM capitals cap
        JOIN countries c ON cap.country_id = c.id
        WHERE cap.latitude IS NOT NULL
          AND cap.longitude IS NOT NULL
      )
      ORDER BY sort_order, country_name
    `);

    return successResponse(rows);
  } catch (error) {
    console.error('Error fetching map data:', error);
    return errorResponse('Failed to fetch map data');
  }
}

// GET /api/statistics
export async function getStatistics(event: APIGatewayProxyEvent): Promise<APIGatewayProxyResult> {
  try {
    const rows = await query(`
      SELECT 
        COUNT(*) FILTER (WHERE status = 'UN member') as member_count,
        COUNT(*) FILTER (WHERE status = 'Territory') as territory_count,
        COUNT(*) FILTER (WHERE status = 'De facto state') as defacto_count,
        COUNT(*) FILTER (WHERE status = 'UN observer') as observer_count,
        COUNT(DISTINCT language) as language_count
      FROM countries
    `);

    return successResponse(rows[0]);
  } catch (error) {
    console.error('Error fetching statistics:', error);
    return errorResponse('Failed to fetch statistics');
  }
} 
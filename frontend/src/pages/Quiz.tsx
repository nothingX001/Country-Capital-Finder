import { useState, useEffect } from 'react';
import '../styles/Quiz.css';

interface QuizCountry {
  id: number;
  country_name: string;
  flag_emoji: string;
  iso_code: string;
  capitals: string[];
}

interface QuizResult {
  country: string;
  capital: string;
  userAnswer: string;
  isCorrect: boolean;
}

const Quiz = () => {
  const [type, setType] = useState<'member' | 'territory'>('member');
  const [limit, setLimit] = useState(10);
  const [countries, setCountries] = useState<QuizCountry[]>([]);
  const [currentIndex, setCurrentIndex] = useState(0);
  const [userAnswer, setUserAnswer] = useState('');
  const [results, setResults] = useState<QuizResult[]>([]);
  const [score, setScore] = useState(0);
  const [isLoading, setIsLoading] = useState(true);
  const [error, setError] = useState('');
  const [isQuizComplete, setIsQuizComplete] = useState(false);
  const [timeLeft, setTimeLeft] = useState(0);

  useEffect(() => {
    const fetchQuiz = async () => {
      setIsLoading(true);
      setError('');

      try {
        const response = await fetch(`/api/quiz?type=${type}&limit=${limit}`);
        const data = await response.json();
        if (data.error) {
          setError(data.error);
          setCountries([]);
        } else {
          setCountries(data);
          setTimeLeft(limit * 30); // 30 seconds per question
        }
      } catch (err) {
        setError('Failed to fetch quiz questions');
        setCountries([]);
      } finally {
        setIsLoading(false);
      }
    };

    fetchQuiz();
  }, [type, limit]);

  useEffect(() => {
    if (timeLeft > 0 && !isQuizComplete) {
      const timer = setInterval(() => {
        setTimeLeft((prev) => {
          if (prev <= 1) {
            clearInterval(timer);
            handleNext();
            return 0;
          }
          return prev - 1;
        });
      }, 1000);

      return () => clearInterval(timer);
    }
  }, [timeLeft, isQuizComplete]);

  const handleAnswer = (e: React.FormEvent) => {
    e.preventDefault();
    if (!userAnswer.trim()) return;

    const currentCountry = countries[currentIndex];
    const correctCapitals = currentCountry.capitals.map((cap) => cap.toLowerCase());
    const isCorrect = correctCapitals.includes(userAnswer.toLowerCase());

    setResults([
      ...results,
      {
        country: currentCountry.country_name,
        capital: currentCountry.capitals[0],
        userAnswer: userAnswer,
        isCorrect,
      },
    ]);

    if (isCorrect) {
      setScore((prev) => prev + 1);
    }

    handleNext();
  };

  const handleNext = () => {
    if (currentIndex < countries.length - 1) {
      setCurrentIndex((prev) => prev + 1);
      setUserAnswer('');
    } else {
      setIsQuizComplete(true);
    }
  };

  const resetQuiz = () => {
    setCurrentIndex(0);
    setUserAnswer('');
    setResults([]);
    setScore(0);
    setIsQuizComplete(false);
    setTimeLeft(limit * 30);
  };

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

  if (isQuizComplete) {
    return (
      <div className="quiz-results">
        <h2>Quiz Complete!</h2>
        <p className="score">Your score: {score} out of {countries.length}</p>
        <div className="detailed-results">
          {results.map((result, index) => (
            <p key={index} className={result.isCorrect ? 'correct' : 'incorrect'}>
              <strong>{result.country}:</strong> {result.capital}
              {!result.isCorrect && ` (Your answer: ${result.userAnswer})`}
            </p>
          ))}
        </div>
        <button className="button" onClick={resetQuiz}>
          Try Again
        </button>
      </div>
    );
  }

  const currentCountry = countries[currentIndex];

  return (
    <div className="quiz">
      <h1>Capital City Quiz</h1>
      <div className="quiz-controls">
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
        <select
          value={limit}
          onChange={(e) => setLimit(Number(e.target.value))}
        >
          <option value="5">5 Questions</option>
          <option value="10">10 Questions</option>
          <option value="20">20 Questions</option>
        </select>
      </div>

      <div className="quiz-progress">
        <p>
          Question {currentIndex + 1} of {countries.length}
        </p>
        <p className="timer">Time left: {timeLeft}s</p>
      </div>

      <div className="question-container">
        <h2>
          <span className="flag-emoji">{currentCountry?.flag_emoji}</span>
          {currentCountry?.country_name}
        </h2>
        <form onSubmit={handleAnswer}>
          <input
            type="text"
            value={userAnswer}
            onChange={(e) => setUserAnswer(e.target.value)}
            placeholder="Enter the capital city..."
            autoFocus
          />
          <button type="submit" className="button">
            Submit
          </button>
        </form>
      </div>
    </div>
  );
};

export default Quiz; 
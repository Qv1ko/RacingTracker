import Link from 'next/link';
import React from 'react';

const Header: React.FC = () => {
  return (
    <header className="sticky top-0 bg-black text-white p-4 drop-shadow-lg">
      <div className="container mx-auto flex justify-between items-center">
        <h1 className="text-xl font-bold">
          <Link href="/">
            RacingTracker
          </Link>
        </h1>
        <nav>
          <ul className="flex space-x-4">
            <li>
              <Link href="/">HOME</Link>
            </li>
            <li>
              <Link href="/drivers">DRIVERS</Link>
            </li>
            <li>
              <Link href="/teams">TEAMS</Link>
            </li>
            <li>
              <Link href="/teams">SEASONS</Link>
            </li>
          </ul>
        </nav>
      </div>
    </header>
  );
};

export default Header;

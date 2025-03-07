import React from 'react';

const Footer: React.FC = () => {
  return (
    <footer className="bg-transparent text-gray mt-8">
      <hr className="w-48 h-1 mx-auto bg-gray-400 border-0 dark:bg-gray-700" />
      <div className="container mx-auto text-center p-4">
        <p>powered by <a href="https://github.com/Qv1ko" target="_blank" rel="noopener noreferrer">Qv1ko</a></p>
      </div>
    </footer>
  );
};

export default Footer;

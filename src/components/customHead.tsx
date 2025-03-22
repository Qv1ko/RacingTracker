import Head from "next/head";
import React from "react";

function CustomHead({ title, description = "", index = false, mediaImagePath = "/images/banner.jpg" }: { title: string; description?: string; index?: boolean; mediaImagePath?: string }) {
  title = "RacingTracker | " + title;

  return (
    <Head>
      <title>{title}</title>
      <meta name="description" content={description} />

      {index ? (
        <>
          <meta name="robots" content="index, follow" />

          <meta property="og:title" content={title} />
          <meta property="og:description" content={description} />
          <meta property="og:image" content={mediaImagePath} />
          {/* <meta property="og:url" content="" /> */}

          <meta name="twitter:card" content="summary_large_image" />
          <meta name="twitter:title" content={title} />
          <meta name="twitter:description" content={description} />
          <meta name="twitter:image" content={mediaImagePath} />
        </>
      ) : null}
    </Head>
  );
}

export default CustomHead;

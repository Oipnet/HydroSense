import { betterAuth } from "better-auth";
import Database from "better-sqlite3";
import { genericOAuth } from "better-auth/plugins";

export const auth = betterAuth({
  database: new Database("./data/auth.db"),
  plugins: [
    genericOAuth({
      config: [
        {
          providerId: "keycloak",
          clientId: process.env.KEYCLOAK_CLIENT_ID || "hydrosense-web-bff",
          clientSecret: process.env.KEYCLOAK_CLIENT_SECRET || "",
          discoveryUrl:
            process.env.KEYCLOAK_DISCOVERY_URL ||
            "http://127.0.0.1:8080/realms/hydrosense/.well-known/openid-configuration",
          scopes: ["openid", "profile", "email"],
          pkce: true,
        },
      ],
    }),
  ],
});

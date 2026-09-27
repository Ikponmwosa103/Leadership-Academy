import crypto from "crypto";
import { cookies } from "next/headers";

const COOKIE = "la_session";
const secret = () => process.env.SESSION_SECRET || "development-only-change-me";

function sign(value) {
  return crypto.createHmac("sha256", secret()).update(value).digest("base64url");
}
export function makeSession(userId) {
  const value = String(userId);
  return `${value}.${sign(value)}`;
}
export function readSession() {
  const token = cookies().get(COOKIE)?.value;
  if (!token) return null;
  const [id, sig] = token.split(".");
  if (!id || !sig || !crypto.timingSafeEqual(Buffer.from(sig), Buffer.from(sign(id)))) return null;
  return Number(id);
}
export function setSession(userId) {
  cookies().set(COOKIE, makeSession(userId), {
    httpOnly:true, sameSite:"lax", secure:process.env.NODE_ENV==="production",
    path:"/", maxAge:60*60*24*30
  });
}
export function clearSession() {
  cookies().set(COOKIE, "", {httpOnly:true, sameSite:"lax", secure:process.env.NODE_ENV==="production", path:"/", maxAge:0});
}

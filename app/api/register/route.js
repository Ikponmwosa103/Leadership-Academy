import { NextResponse } from "next/server";
import bcrypt from "bcryptjs";
import { getPool, ensureTables } from "../../../lib/db";
export async function POST(req){
 try{
  const d=await req.json(); const name=String(d.name||d.full_name||"").trim(), email=String(d.email||"").trim().toLowerCase(), phone=String(d.phone||"").trim(), password=String(d.password||"");
  if(!name||!email||!phone||!password)return NextResponse.json({success:false,message:"Please complete all fields."},{status:400});
  if(name.length<2)return NextResponse.json({success:false,message:"Please enter your full name."},{status:400});
  if(!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email))return NextResponse.json({success:false,message:"Please enter a valid email address."},{status:400});
  if(phone.length<7)return NextResponse.json({success:false,message:"Please enter a valid phone number."},{status:400});
  if(password.length<8)return NextResponse.json({success:false,message:"Password must be at least 8 characters."},{status:400});
  await ensureTables(); const db=getPool(); const [existing]=await db.execute("SELECT id FROM users WHERE email=? LIMIT 1",[email]);
  if(existing[0])return NextResponse.json({success:false,error:"email_exists",message:"An account with this email already exists."},{status:409});
  const hash=await bcrypt.hash(password,12); const [r]=await db.execute("INSERT INTO users(full_name,email,phone,password_hash) VALUES(?,?,?,?)",[name,email,phone,hash]);
  return NextResponse.json({success:true,message:"Registered successfully.",user:{id:r.insertId,name,email,phone}},{status:201});
 }catch(e){console.error(e);return NextResponse.json({success:false,message:"Unable to create your account. Please try again."},{status:500})}
}